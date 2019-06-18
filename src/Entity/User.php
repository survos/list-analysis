<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use MsgPhp\User\User as BaseUser;
use MsgPhp\User\UserId;
use MsgPhp\Domain\Event\DomainEventHandler;
use MsgPhp\Domain\Event\DomainEventHandlerTrait;
use MsgPhp\User\Credential\NicknamePassword;
use MsgPhp\User\Model\NicknamePasswordCredential;
use MsgPhp\User\Model\ResettablePassword;
use MsgPhp\User\Model\RolesField;

/**
 * @ORM\Entity()
 * @ORM\Table(name="app_user")
 */
class User extends BaseUser implements DomainEventHandler
{
    use DomainEventHandlerTrait;
    use NicknamePasswordCredential;
    use ResettablePassword;
    use RolesField;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="msgphp_user_id", length=191)
     */
    private $id;

    public function __construct(UserId $id, string $nickname, string $password)
    {
        $this->id = $id;
        $this->credential = new NicknamePassword($nickname, $password);
    }

    public function getId(): UserId
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->getCredential()->getUsername();
    }

}
