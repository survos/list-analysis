<?php

namespace App\Form;

use App\Entity\Invitation;
use App\Form\DataTransformer\InvitationToCodeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvitationFormType extends AbstractType
{

    private $invitationTransformer;

    public function __construct(InvitationToCodeTransformer $invitationTransformer)
    {
        $this->invitationTransformer = $invitationTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->invitationTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'class' => Invitation::class,
            'required' => true,
        ));
    }

    public function getParent()
    {
        return TextType::class;

        // Or for Symfony < 2.8
        // return 'text';
    }

    public function getBlockPrefix()
    {
        return 'app_invitation_type';
    }

    // Not necessary on Symfony 3+
    public function getName()
    {
        return 'app_invitation_type';
    }

}
