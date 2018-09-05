<?php
/**
 * Created by PhpStorm.
 * User: tac
 * Date: 9/4/18
 * Time: 8:43 PM
 */

namespace App\Services;


class MailArchiveService
{

    private $currentLine;
    private $messageCount;

    private $lines = [];

    public function __construct()
    {
    }

    public function init($archiveContent): self
    {
        $this->lines = explode("\n", $archiveContent);
        $this->currentLine = 1;
        $this->messageCount = 0;

        return $this;
    }

    public function import()
    {
        $body = $this->lines[0]; // first one is a new message
        $c = 0;


        while ($this->currentLine < count($this->lines)) {

            do {
                $line = $this->lines[$this->currentLine];

                if (preg_match('/(From|Date|Subject|In-Reply-To|References|Message-Id): (.*?)$/', $line, $m)) {
                    list($dummy, $attribute, $value) = $m;
                    $header[$attribute] = $value;
                } else {
                    $body .= "\n" . $line;
                }

                $this->currentLine++;

            } while ($this->currentLine < count($this->lines) && !preg_match('/From .*?20\d\d$/', $line,$m));
            $this->messageCount++;


            printf("Message %d\n\t%s\n\n", $this->messageCount, $body);
            print_r($header);

            $body = '';
            $header = [];
        }


    }

}