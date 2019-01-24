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
            ->add('startDate', DateType::class, [
                'label' => "From",
                'widget' => 'single_text',
                'attr' =>
                    [
                        'min' => '2006-01-01',
                        'max' => '2018-12-31'
                    ]
                // 'input' => 'string'
            ])
            ->add('endDate', DateType::class, [
                'label' => "To",
                    'widget' => 'single_text',
            ])
            ->add('accountLimit', IntegerType::class, [
                'label' => "Top Posters",
                'attr' =>
                    [
                        'min' => 1,
                        'max' => 30,
                        // 'class' => 'form-control input-sm col-md-2 ',
                        // 'width' => '60px'
                    ]

            ])
            // ->add('endDate')

            // ->add('search', SubmitType::class)
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
