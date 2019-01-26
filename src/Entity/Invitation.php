<?php
// src/AppBundle/Entity/Invitation.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity */
class Invitation
{
    /** @ORM\Id
     *  @ORM\Column(type="string", length=6)
     */
    protected $code;

    /** @ORM\Column(type="string", length=256) */
    protected $email;

    /**
     * When sending invitation be sure to set this value to `true`
     *
     * It can prevent invitations from being sent twice
     *
     * @ORM\Column(type="boolean")
     */
    protected $sent = false;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $pendingData;

    public function __construct()
    {
        // generate identifier only once, here a 6 characters length code
        $this->code = substr(md5(uniqid(rand(), true)), 0, 6);
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function isSent()
    {
        return $this->sent;
    }

    public function send()
    {
        $this->sent = true;
    }

    public function getPendingData()
    {
        return $this->pendingData;
    }

    public function setPendingData($pendingData): self
    {
        $this->pendingData = $pendingData;

        return $this;
    }
}
