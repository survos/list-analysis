<?php

namespace App\Command;

use App\Entity\Archive;
use App\Services\MailArchiveService;
use Doctrine\ORM\EntityManagerInterface;
use Goutte\Client;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Thruway\Peer\ClientInterface;
use Voryx\ThruwayBundle\Client\ClientManager;

class DownloadCommand extends Command
{

    protected static $defaultName = 'app:download';

    private $em;
    private $archiveService;
    private $thruwayClient;

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('filename', InputArgument::OPTIONAL, 'filename, e.g. 2007-January.txt.gz' )
            ->addOption('refresh', null, InputOption::VALUE_NONE, 'Refresh Monthly List')
            ->addOption('purge', null, InputOption::VALUE_NONE, 'Purge before load')
        ;
    }

    public function __construct(EntityManagerInterface $entityManager,
                                ClientManager $thruwayClient,
                                MailArchiveService $mailArchiveService, $name = null)
    {
        parent::__construct($name);
        $this->em = $entityManager;
        $this->archiveService = $mailArchiveService;
        $this->thruwayClient = $thruwayClient;
    }

    public function publish($value)
    {
        print "Publishing!...\n";
        // $client = $this->container->get('thruway.client');
        $client = $this->thruwayClient; //


        $client->publish("download.rappnew", [$value]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $base = 'http://list.rappnet.org/mailman/private/rappnet_list.rappnet.org/';



// makes a real request to an external site
        $client = new Client();
        $crawler = $client->request('GET', $base);

// select the form and fill in some values
        $form = $crawler->selectButton('submit')->form();
        $form['username'] = 'tacman@gmail.com';
        $form['password'] = 'ozwoupid';


// submits the given form
        $crawler = $client->submit($form);

        /*

        $crawler = $client->request('GET', 'http://list.rappnet.org/mailman/private/rappnet_list.rappnet.org/2018-September.txt.gz');
        $io->writeln(sprintf("%s bytes", $client->getResponse()->getContent())); die();
        */

        // hack, regex the links
        $text = $client->getResponse();

        // this doesn't seem quite right
        $em = $this->em; // $this->getContainer()->get('doctrine');
        $repo = $em->getRepository(Archive::class);

        if ($input->getOption('purge'))
        {
            array_map(function (Archive $a) {
                $this->em->remove($a);
            }, $repo->findAll());
            $this->em->flush();
        }

        if ($input->getOption('refresh'))
        {

            if (preg_match_all('/href="(.*?\.gz)"/', $text, $mm))
            {
                $files = array_reverse($mm[1]);
                foreach ($files as $filename) {
                    $io->text("Checking " . $filename);
                    if (!$archive = $repo->findOneBy(['filename' => $filename])) {
                        $archive = (new Archive())
                            ->setFilename($filename);
                        $em->persist($archive);
                    }
                }
            } else {
                $io->error("No gz files found");
            }
            $em->flush();
        }

        /*

        $cookieJar = $client->getCookieJar();
        $guzzleClient = $client->getClient();
        $jar = CookieJar::fromArray($cookieJar->all(), 'list.rappnet.org');
        dump($jar, $cookieJar);
        */

        if ($filename = $input->getArgument('filename')) {
            $filter = ['filename' => $filename];
        } else {
            $filter = [
                'marking' => Archive::PLACE_NEW
            ];
        }

        // 'filename' => '2006-April.txt.gz'
        foreach ($repo->findBy($filter, ['id' => 'ASC']) as $archive) {
            $url = $base . $archive->getFilename();
            $savedFile = "../data/" . $archive->getFilename();

            if (!file_exists($savedFile)) {
                $crawler = $client->request('GET', $url);
                file_put_contents($savedFile, $client->getResponse()->getContent());
                $io->writeln(sprintf("%s: %s bytes", $savedFile, filesize($savedFile)));
            }

            $archive
                ->setZippedFileSize(filesize($savedFile)); // could also check the content size
            ;


            $this->publish($savedFile);
            sleep(10);



            /*
            $content = file_get_contents($savedFile);

            $this->archiveService->init($content, $archive)
                ->import();


            $response = $guzzleClient->get($url, ['sink' => $savedFile]);
            $io->writeln($savedFile . " downloaded: " . filesize($savedFile));
            die("Stopped");

            $response = $client->request('GET', $url, [ 'sink' => $savedFile]);
            $client->getResponse()->getContent();

            $io->writeln($savedFile . " downloaded: " . filesize($savedFile));
            die("Stopped");


            $response = $guzzleClient->get($url, ['cookies' => $jar, 'sink' => $savedFile]);

            $io->writeln($savedFile . " downloaded: " . filesize($savedFile));
            die("Stopped");
            */
        }






        /* this is better..
        foreach ($crawler->links() as $link) {
            dump($link); die("Link");
        }
        $crawler->filter('a')->each(function ($node) {
            print $node->text()."\n";
            dump($node); die("Stopped after node dump");
        });
        */

        // dump the results
        // dump($client->getResponse());

        $em->flush();
        $io->success('Finished download.');

    }

}
