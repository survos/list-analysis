<?php

namespace App\Command;

use App\Entity\Account;
use App\Entity\Message;
use App\Entity\TimePeriod;
use Doctrine\Common\Cache\SQLite3Cache;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\DoctrineAdapter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppImportArchivesCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'app:import-archives';

    private $accounts;

    /** @var ApcuAdapter */
    private $cache;

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager');
    }

    private function findOrCreateAccount($from): Account {
        // normalize $from
        $senderName = '';
        if (!strpos($from, ' at ')) {
            throw new \Exception("Invalid From: $from");
        }
        if (preg_match('/ \(([^)]*)\)/', $from, $m) )
        {
            $senderName = $m[1];
            // might be blank, x at y.com ()
            if ($m[0]) {
                $from = trim(str_replace($m[0], '', $from));
            }
            $from = trim(strtolower($from));
        }

        $em = $this->getEntityManager();
        $accountRepo = $em->getRepository(Account::class);
        if (empty($this->accounts[$from])) {
            if (!$account = $accountRepo->findOneBy(['sender' => $from]))
            {
                $account = (new Account())
                    ->setSenderName($senderName)
                    ->setSender($from);
                $em->persist($account);
            } else {
                $em->persist($account);
            }
            $this->accounts[$from] = $account;
        } else {
            // printf("$from already exists");
        }
        return $this->accounts[$from];

    }

    private function initAccounts()
    {
        foreach ($this->getEntityManager()->getRepository(Account::class)->findAll() as $account) {
            $this->accounts[$account->getSender()] = $account;
        }
    }

    private function getTimePeriod($year, $month): TimePeriod
    {
        $em = $this->getEntityManager();
        if (!$timePeriod = $em->getRepository(TimePeriod::class)->findOneBy([
            'year' => $year,
            'monthNumber' => $month
        ]) ) {
            $timePeriod = (new TimePeriod())
                ->setMonthNumber($month)
                ->setYear($year)
            ;
            $em->persist($timePeriod);
        }
        return $timePeriod;

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $provider = new SQLite3Cache(new \SQLite3($fn = './var/cache/data.sqlite'), 'messages');
        } catch (\Exception $e) {
            die("Can't open $fn");
        }

        $this->cache = new DoctrineAdapter($provider);

        // $this->cache = new ApcuAdapter();
        $this->cache->clear();

        $this->initAccounts();
        $io = new SymfonyStyle($input, $output);

        $em = $this->getEntityManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        // for each month, starting in Jan, 2006
        for ($year=2005; $year<=date('Y'); $year++) {
            for ($month=1; $month<=12; $month++) {
                $filename = sprintf('/var/www/rappnettext/%s-%s.txt.gz', $year,
                    \DateTime::createFromFormat('!m', $month)->format('F'));
                if (!file_exists($filename)) {
                    $io->writeln("Skipping $filename");
                    continue;
                }
                $timePeriod = $this->getTimePeriod($year, $month);
                $count = $this->parseFile($filename, $io, $timePeriod);
                try {
                    $this->getEntityManager()->flush();
                    $this->getEntityManager()->clear();
                    $this->initAccounts();
                } catch (\Exception $e) {
                    die($e->getMessage() . "\n");
                }
                // $this->getEntityManager()->clear(); // reset, too slow otherwise
                $io->success(sprintf("%d messages in %s, Accounts: %d", $count, $filename, count($this->accounts) ));
                // if ($month == 2) die("Stopped");
            }
        }
    }

    private function parseFile($fn, SymfonyStyle $io, TimePeriod $month)
    {
        $data = file_get_contents($fn);
        $md5 = md5($data);

        if ($month->getMd5() == $md5) {
            return true; // already loaded...
        }
        $month
            ->setMd5(md5($data));
        $io->writeln("Reading $fn");
        // look for the From line as a delimited
        if ($messages = preg_split('/^From [^$]*? (Mon|Tue|Wed|Thu|Fri|Sat|Sun) /m', $data))
        {
            $message = array_shift($messages); // first one is always blank
            $month->setCalculatedMessageCount(count($messages));
            $progress = $io->createProgressBar(count($messages));
            $progress->setMessage("Reading $fn");

            $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
            $progress->setRedrawFrequency(100);
            foreach ($messages as $message) {
                $progress->advance();
                if ($message = $this->importMessage($message, $month)) {
                    $messageItem = $this->cache->getItem($message->getId());
                    if (!$messageItem->isHit()) {
                        $messageItem->set(0);
                        $this->cache->save($messageItem);
                    }
                    // $month->addMessage($message);
                    // $message->setTimePeriod($month);
                }
            }
            $progress->finish();
            return count($messages);
        } else {
            throw new \Exception("No messages found in $fn");
        }
    }

    private function importMessage($message, TimePeriod $timePeriod): ?Message {
        if (empty($message))
        {
            return null;
        }

        $em = $this->getEntityManager();
        $messageRepository = $em->getRepository(Message::class);

        // dump($message);

        $lines = explode("\n", $message);
        // first, parse the headers
        $line = array_shift($lines); // should be /From .../
        if (!preg_match('/ 20\d\d$/', $line)) {
            dump($message);
            throw new \Exception("Error in message header, expecting date, got $line");
        }
        $header = $bodyLines =  [];
        $key = null;
        while ($headerLine = array_shift($lines)) {
            // if there's no :, the value belongs to the previous header!
            // if (!strpos($headerLine, ':') ) {
            if ( preg_match('/^\s/', $headerLine) ) {
                $header[$key] .= $headerLine;
            } elseif (strpos($headerLine, ':')) {
                list($key, $value) = explode(':', $headerLine, 2);
                $header[$key] = $value;
            } else {
                dump($headerLine); die("Bad headerline: $headerLine");
            }
        }

        try {
            $header = (new OptionsResolver())
                ->setDefaults([
                    'In-Reply-To' => null,
                    'References' => null
                ])->setRequired([
                    'Message-ID',
                    'Subject',
                    'From',
                    'Date'
                ])->resolve($header);
        } catch (\Exception $e) {
            dump($message, $header);
            die($e->getMessage());
        }
        // now insert/update the record
        $messageId = $this->cleanMessageId($header['Message-ID']);

        if (!$message = $messageRepository->find($messageId) ) {
            $subject = $header['Subject'];
            $subject = str_replace('[Rappnet]', '', $subject);
            $subject = trim($subject);

            $date = $header['Date'];
            try {
                $sendTime = new \DateTime($date);
            } catch (\Exception $e) {
                // try stripping off the extra timezone
                $date = trim(preg_replace('/\(.*?\)/', '', $date));
                $sendTime = new \DateTime($date);
            }

            // get the account
            $from = $header['From'];
            $account = $this->findOrCreateAccount($from);
            // dump(array_keys($this->accounts));
            $message = (new Message())
                ->setId($messageId)
                ->setTimePeriod($timePeriod)
                ->setAccount($account)
                ->setFromText($from)
                ->setSubject($subject)
                ->setTime($sendTime)
                ->setBody(join("\n", $lines)) // because the header has already been read, rawBody
                // ->setMessageId($messageId)
                ->setInReplyToMessageId($this->cleanMessageId($header['In-Reply-To']))
            ;
            $timePeriod->addMessage($message);
            // make sure it's in the cache
            if ($rawParentMessageId = $header['In-Reply-To']) {
                $parentMessageId = $this->cleanMessageId($rawParentMessageId);
                if ($messageItem = $this->cache->getItem($parentMessageId) ) {
                    if ($messageItem->isHit()) {
                        $message->setInReplyTo(
                            $em->getReference(Message::class, $parentMessageId)
                        );
                        $messageItem->set($messageItem->get()+1);
                        $this->cache->save($messageItem);
                    }
                }
            }

            if ($header['References']) {
                // dump($header); die();
            }

            // $account->addMessage($message);
            $em->persist($message);
        } else {
            // $em->detach($message);
        }

        /*
        $cacheItem = $this->cache->getItem($message->getId());
        $cacheItem->set($message); // hmm, seems like a lot of memory?  really we just want a reference.
        $this->cache->save($cacheItem);
        */
        // @todo: move this to processBody
        /*
        while ( ( ($bodyLine = array_shift($lines)) !== null) && !preg_match('/^-- /', $bodyLine)) {
            $bodyLines[] = $bodyLine;
        }
        $body = join("\n", $bodyLines); // could be " ", too.
        */
        if (0)
        try {
            $em->flush($timePeriod); //
            $em->flush($message); //
        } catch (\Exception $exception) {
            dump($message);
            die($exception->getMessage());
        }
        return $message; // unless we want to reprocess

        // dump($header, $body);

        // what's left, without the previously quoted text

        // $em->flush($message);
    }

    private function cleanMessageId($messageId)
    {
        if ($messageId) {
            $messageId = str_replace('<', '', $messageId);
            $messageId = str_replace('>', '', $messageId);
            return md5(trim($messageId));
        } else {
            return null;
        }
    }

    private function getInReplyToMessage($messageId)
    {
        // return null;
        if ($message =  $this->cache->getItem($messageId)->get()) {
            // dump($message);
        }
        return $message;

    }
}
