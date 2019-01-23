<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArchiveRepository")
 */
class Archive
{

    const PLACE_IMPORTED = 'imported';
    const PLACE_NEW = 'new';
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $filename;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $messageCount;

    /**
     * @ORM\Column(type="string", length=32, nullable=false)
     */
    private $marking;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $lineCount;

    /**
     * @ ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="archive", orphanRemoval=true)
     */
    private $messages;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $zippedFileSize;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->marking = self::PLACE_NEW;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getMessageCount(): ?int
    {
        return $this->messageCount;
    }

    public function setMessageCount(?int $messageCount): self
    {
        $this->messageCount = $messageCount;

        return $this;
    }

    public function getMarking(): ?string
    {
        return $this->marking;
    }

    public function setMarking(?string $marking): self
    {
        $this->marking = $marking;

        return $this;
    }

    public function getLineCount(): ?int
    {
        return $this->lineCount;
    }

    public function setLineCount(?int $lineCount): self
    {
        $this->lineCount = $lineCount;

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setArchive($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getArchive() === $this) {
                $message->setArchive(null);
            }
        }

        return $this;
    }

    public function getZippedFileSize(): ?int
    {
        return $this->zippedFileSize;
    }

    public function setZippedFileSize(?int $zippedFileSize): self
    {
        $this->zippedFileSize = $zippedFileSize;

        return $this;
    }
}
