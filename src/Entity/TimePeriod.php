<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TimePeriodRepository")
 */
class TimePeriod
{
    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->messageCount = 0;
        $this->locMessageCount = 0;
        $this->subjectCount = 0;
    }

    public function __toString()
    {
        return sprintf('%s/%d', $this->getMonthNumber(), $this->getYear());
        // TODO: Implement __toString() method.
    }


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="timePeriod", cascade={"persist"})
     */
    private $messages;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $monthNumber;

    /**
     * @ORM\Column(type="integer")
     */
    private $year;

    /**
     * @ORM\Column(type="integer")
     */
    private $calculatedMessageCount;

    /**
     * @ORM\Column(type="integer")
     */
    private $importedMessageCount;

    /**
     * @ORM\Column(type="integer")
     */
    private $locMessageCount;

    /**
     * @ORM\Column(type="integer")
     */
    private $subjectCount;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $md5;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return TimePeriod
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMonthNumber()
    {
        return $this->monthNumber;
    }

    /**
     * @param mixed $monthNumber
     * @return TimePeriod
     */
    public function setMonthNumber($monthNumber)
    {
        $this->monthNumber = $monthNumber;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param mixed $year
     * @return TimePeriod
     */
    public function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocMessageCount()
    {
        return $this->locMessageCount;
    }

    /**
     * @param mixed $locMessageCount
     * @return TimePeriod
     */
    public function setLocMessageCount($locMessageCount)
    {
        $this->locMessageCount = $locMessageCount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubjectCount()
    {
        return $this->subjectCount;
    }

    /**
     * @param mixed $subjectCount
     * @return TimePeriod
     */
    public function setSubjectCount($subjectCount)
    {
        $this->subjectCount = $subjectCount;
        return $this;
    }


    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    /**
     * @param mixed $messages
     * @return TimePeriod
     */
    public function setMessages($messages): TimePeriod
    {
        $this->messages = $messages;
        return $this;
    }

    public function addMessage(Message $message) {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setTimePeriod($this);
            $this->setImportedMessageCount($this->messages->count());
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCalculatedMessageCount()
    {
        return $this->calculatedMessageCount;
    }

    /**
     * @param mixed $calculatedMessageCount
     * @return TimePeriod
     */
    public function setCalculatedMessageCount($calculatedMessageCount)
    {
        $this->calculatedMessageCount = $calculatedMessageCount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getImportedMessageCount()
    {
        return $this->importedMessageCount;
    }

    /**
     * @param mixed $importedMessageCount
     * @return TimePeriod
     */
    public function setImportedMessageCount($importedMessageCount)
    {
        $this->importedMessageCount = $importedMessageCount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMd5()
    {
        return $this->md5;
    }

    /**
     * @param mixed $md5
     * @return TimePeriod
     */
    public function setMd5($md5)
    {
        $this->md5 = $md5;
        return $this;
    }

    /**
     * @return int
     */
    public function getMessageCount(): int
    {
        return $this->messageCount;
    }

    /**
     * @param int $messageCount
     * @return TimePeriod
     */
    public function setMessageCount(int $messageCount): TimePeriod
    {
        $this->messageCount = $messageCount;
        return $this;
    }

    public function getRouteParams()
    {
        return ['id'=>$this->getId()];
    }


}
