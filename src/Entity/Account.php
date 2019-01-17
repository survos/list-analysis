<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccountRepository")
 * @ORM\Table(indexes={@ORM\Index(name="count_idx", columns={"count"})})
 * @ApiResource(
 *     normalizationContext={"groups"={"Default"}},
 *     denormalizationContext={"groups"={"Default","export" }}
 * )
 */
class Account
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("Default")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     */
    private $sender;

    /**
     * @ORM\Column(type="string", length=80)
     * @Groups("Default")
     */
    private $senderName;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $count;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="account", fetch="EXTRA_LAZY")
     */
    private $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getSenderName() ?: $this->getSender();
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
     * @return Account
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param mixed $sender
     * @return Account
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param mixed $count
     * @return Account
     */
    public function setCount($count)
    {
        $this->count = $count;
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
     * @return Account
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
        return $this;
    }

    public function addMessage(Message $message)
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setAccount($this);
            $this->setCount($this->messages->count());
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSenderName()
    {
        return $this->senderName;
    }

    /**
     * @param mixed $senderName
     * @return Account
     */
    public function setSenderName($senderName)
    {
        $this->senderName = substr($senderName, 0, 80);
        return $this;
    }


}
