<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessageRepository")
 */
class Message
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
    private $id;
     */

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Account", inversedBy="messages")
     */
    private $account;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="inReplyTo")
     */
    private $replies;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TimePeriod", inversedBy="messages")
     */
    private $timePeriod;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Message", inversedBy="replies")
     */
    private $inReplyTo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
    private $inReplyToMessageId;
     */

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    private $fromText; // original $header['From']

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $time;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    private $body;

    /**
     * @ORM\Column(type="string", length=160, nullable=false)
     */
    private $subject;


    public function __toString()
    {
        return substr($this->getSubject() . ": " . $this->getBody(), 0, 80);
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     * @return Message
     */
    public function setSubject($subject)
    {
        $this->subject = substr(utf8_encode($subject), 0, 160);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param mixed $time
     * @return Message
     */
    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSenderName()
    {
        return $this->getAccount()->getSender();
    }

    /**
     * @param mixed $senderName
     * @return Message
     */
    public function setSenderName($senderName)
    {
        $this->senderName = $senderName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @param mixed $messageId
     * @return Message
     */
    public function setMessageId($messageId)
    {
        $this->messageId = substr($messageId, 0, 255);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     * @return Message
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Message
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccount(): Account
    {
        return $this->account;
    }

    /**
     * @param mixed $account
     * @return Message
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInReplyTo(): ?Message
    {
        return $this->inReplyTo;
    }

    /**
     * @param mixed $inReplyTo
     * @return Message
     */
    public function setInReplyTo(?Message $inReplyTo)
    {
        $this->inReplyTo = $inReplyTo;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInReplyToMessageId()
    {
        return $this->inReplyToMessageId;
    }

    /**
     * @param mixed $inReplyToMessageId
     * @return Message
     */
    public function setInReplyToMessageId($inReplyToMessageId)
    {
        $this->inReplyToMessageId = $inReplyToMessageId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFromText()
    {
        return $this->fromText;
    }

    /**
     * @param mixed $fromText
     * @return Message
     */
    public function setFromText($fromText)
    {
        $this->fromText = $fromText;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTimePeriod()
    {
        return $this->timePeriod;
    }

    /**
     * @param mixed $timePeriod
     * @return Message
     */
    public function setTimePeriod(TimePeriod $timePeriod)
    {
        $this->timePeriod = $timePeriod;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReplies()
    {
        return $this->replies;
    }

    /**
     * @param mixed $replies
     * @return Message
     */
    public function setReplies($replies)
    {
        $this->replies = $replies;
        return $this;
    }

}
