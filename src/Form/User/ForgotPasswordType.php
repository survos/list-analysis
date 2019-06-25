<?php

declare(strict_types=1);

namespace App\Form\User;

use MsgPhp\User\Infrastructure\Validator\ExistingUsername as ExistingNickname;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ForgotPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nickname', TextType::class, [
            'constraints' => [new NotBlank(), new ExistingNickname()],
        ]);
    }
}
