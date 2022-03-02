<?php

namespace App\Form;

use App\Entity\Agent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Languages;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgentAccountType extends AbstractType
{
    public function __construct(
        /**
         * @var array<string> $locales
         */
        private readonly array $locales
    ) {
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add(
                'real_name',
                null,
                [
                    'label' => 'Real Name',
                ]
            )
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
            )
            ->add(
                'hasNotifyEvents',
                null,
                [
                    'label' => 'notify.events',
                ]
            )
            ->add(
                'hasNotifyUploadStats',
                null,
                [
                    'label' => 'notify.stats.upload',
                ]
            )
            ->add(
                'hasNotifyStatsResult',
                null,
                [
                    'label' => 'notify.stats.result',
                ]
            )
            ->add(
                'locale',
                ChoiceType::class,
                [
                    'label'        => 'select.locale',
                    'choices'      => $this->locales,
                    'choice_label' => static function ($value) {
                        $name = Languages::getName($value);
                        $localName = Languages::getName($value, $value);

                        // @TODO Español is written with a lower case letter :(
                        $name = ucfirst($name);
                        $localName = ucfirst($localName);

                        return $name ? "$value - $localName ($name)" : $value;
                    },
                    'attr'         => [
                        'class'      => 'selectpicker',
                        'data-style' => 'btn-success',
                    ],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Agent::class,
            ]
        );
    }
}
