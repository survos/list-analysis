<?php

declare(strict_types=1);

namespace App\Console;

use App\Entity\User;
use MsgPhp\Domain\Infrastructure\Console\Context\ClassContextElementFactory as BaseClassContextElementFactory;
use MsgPhp\Domain\Infrastructure\Console\Context\ContextElement;
use MsgPhp\User\Credential\NicknamePassword;
use MsgPhp\User\Password\PasswordHashing;

final class ClassContextElementFactory implements BaseClassContextElementFactory
{
    private $passwordHashing;

    public function __construct(PasswordHashing $passwordHashing)
    {
        $this->passwordHashing = $passwordHashing;
    }

    public function getElement(string $class, string $method, string $argument): ContextElement
    {
        $element = new ContextElement(ucfirst((string) preg_replace(['/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'], ['\\1 \\2', '\\1 \\2'], $argument)));

        switch ($argument) {
            case 'password':
                if (User::class === $class || NicknamePassword::class === $class) {
                    $element
                        ->hide()
                        ->generator(function (): string {
                            return bin2hex(random_bytes(8));
                        })
                        ->normalizer(function (string $value): string {
                            return $this->passwordHashing->hash($value);
                        })
                    ;
                }
                break;
        }

        return $element;
    }
}
