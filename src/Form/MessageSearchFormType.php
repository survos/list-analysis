<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageSearchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('accountLimit', IntegerType::class, [
                'attr' =>
                    [
                        'min' => 1,
                        'max' => 20
                    ]

            ])
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                // 'input' => 'string'
            ])
            ->add('endDate', DateType::class, [
                    'widget' => 'single_text',
            ])
            // ->add('endDate')

            ->add('search', SubmitType::class)
            ->setMethod('GET')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            // Configure your form options here
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
