<?php

namespace App\Form;

use App\Entity\IngressEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<IngressEvent>
 */
class IngressEventType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('name')
            ->add(
                'type',
                ChoiceType::class,
                [
                    'choices' => [
                        'IFS'   => 'fs',
                        'MD'    => 'md',
                        'EVENT' => 'event',
                    ],
                ]
            )
            ->add('link')
            ->add(
                'date_start',
                DateTimeType::class,
                [
                    'widget' => 'single_text',
                ]
            )
            ->add(
                'date_end',
                DateTimeType::class,
                [
                    'widget' => 'single_text',
                ]
            )
            ->add(
                'description',
                null,
                [
                    'attr'  => ['rows' => 10],
                    'label' => 'label.content',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => IngressEvent::class,
            ]
        );
    }
}
