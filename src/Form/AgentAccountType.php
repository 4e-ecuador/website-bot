<?php

namespace App\Form;

use App\Entity\Agent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgentAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'real_name',
                null,
                [
                    'label' => 'Real Name',
                ]
            )
            ->add('telegramName')
            ->add('recursions', null, ['attr' => ['min' => 0]])
            ->add('customMedals')
            ->add(
                'lat',
                NumberType::class,
                [
                    'required' => false,
                    'scale'    => 7,
                    'attr'     => [
                        'min'  => -90,
                        'max'  => 90,
                        'step' => 0.0000001,
                    ],
                ]
            )
            ->add(
                'lon',
                NumberType::class,
                [
                    'required' => false,
                    'scale'    => 7,
                    'attr'     => [
                        'min'  => -90,
                        'max'  => 90,
                        'step' => 0.0000001,
                    ],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Agent::class,
            ]
        );
    }
}
