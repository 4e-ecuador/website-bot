<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('name')
            ->add(
                'eventType',
                ChoiceType::class,
                [
                    'choices' => [
                        'AP'           => 'ap',
                        'Hacker'       => 'hacker',
                        'Builder'      => 'builder',
                        'Trekker'      => 'trekker',
                        'Purifier'     => 'purifier',
                        'Recharger'    => 'recharger',
                        'OPR'          => 'recon',
                        'Fields/Links' => 'fieldslinks',
                    ],
                ]
            )
            ->add('date_start')
            ->add('date_end');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Event::class,
            ]
        );
    }
}
