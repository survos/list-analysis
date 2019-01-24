<?php
/**
 * Created by PhpStorm.
 * User: tac
 * Date: 9/4/18
 * Time: 8:43 PM
 */

namespace App\Services;


use App\Entity\Archive;
use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;

class MailArchiveService
{

    private $currentLine;
    private $messageCount;

    /** @var Archive */
    private $archive;

    private $em;
    private $messageRepo;

    private $lines = [];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->messageRepo = $entityManager->getRepository(Message::class);
    }

    public function init($archiveContent, Archive $archive): self
    {
        // $this->lines = preg_split( '/\r\n|\r|\n/', $archiveContent ); // preg_split("\r\n", $archiveContent);

        $this->lines = explode("\n", $archiveContent);
        $this->currentLine = 1;
        $this->messageCount = 0;
        $this->archive = $archive;
        $archive->setLineCount(count($this->lines));
        $this->em->flush();

        return $this;
    }

    private function import()
    {
        $archive = $this->archive;
        $body = $this->lines[0]; // first one is a new message
        $c = 0;
        $inHeader = true;
        $messageIds = [];


        while ($this->currentLine < count($this->lines)) {

            do {
                $line = $this->lines[$this->currentLine];
                if (empty($line) && $inHeader) {
                    $inHeader = false;
                }



                if ($inHeader && preg_match('/^(From|Date|Subject|In-Reply-To|References|Message-ID): (.*?)$/', $line, $m)) {
                    list($dummy, $attribute, $value) = $m;
                    $header[$attribute] = $value;
                } else {
                    $body .= "\n" . $line;
                }

                $this->currentLine++;

            } while ($this->currentLine < count($this->lines) && !preg_match('/From .*?20\d\d$/', $line,$m));
            $this->messageCount++;


            // add the message
            if (empty($header['Message-ID'])) {
                continue; // for now...
                print $body;
                print_r($header);
                print $this->archive->getFilename();
            } else {
                $messageId = $header['Message-ID'];
            }

            // $date = \DateTime::createFromFormat('D M d Y H:i:s e+', $header['Date']);
            if (empty($header['Date'])) {
                dump($header, $body);
                continue; // skip it for now
                throw new \Exception(sprintf("Error on line %d, no Date field", $this->currentLine, $this->archive->getFilename()));
            }
            $dateString = $header['Date'];
            try {
                $date = new \DateTime($dateString);
            } catch (\Exception $e) {
                // get rid of the stuff in parens and try again.
                $dateString = preg_replace('/\(.*\)/', '', $dateString);
                $dateString = str_replace('*', '', $dateString);
                $date = new \DateTime($dateString);
            }

            // check for duplicate id's, sometimes happens
            if (in_array($messageId, $messageIds)) {
                continue;
            }

            if ( !$message = $this->messageRepo->findOneBy(['messageId' => $messageId])) {

                $message = (new Message())
                    ->setMessageId($messageId);
                $this->em->persist($message);
            }

            $subject = $header['Subject'];
            // clean up

            $message
                ->setArchive($archive)
                ->setSender($header['From'])
                ->setDate($date)
                ->setSubject($subject)
                ->setBody($body);
            /*
            printf("Message %d\n\t%s\n\n", $this->messageCount, $body);
            print_r($header);
            */

            $body = '';
            $header = [];
            $inHeader = true;
            array_push($messageIds, $messageId);
            // $this->em->flush();

        }

        $this->archive
            ->setMarking(Archive::PLACE_IMPORTED)
            ->setMessageCount($this->messageCount);

        printf("%s messages in %s\n", $this->archive->getMessageCount(), $this->archive->getFilename());

        $this->em->flush();

    }

}