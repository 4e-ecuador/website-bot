<?php

namespace App\Form;

use App\Entity\IngressEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IngressEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add(
                'type',
                ChoiceType::class, [
                    'choices' => [
                        'IFS' => 'fs',
                        'MD'  => 'md',
                    ],
                ]
            )
            ->add('link')
            ->add(
                'date_start',
                DateType::class, [
                    'widget' => 'single_text',
                ]
            )
            ->add(
                'date_end',
                DateType::class, [
                    'widget' => 'single_text',
                ]
            )
            ->add('description');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => IngressEvent::class,
            ]
        );
    }
}
