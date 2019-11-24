<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('eventType', ChoiceType::class, [
                'choices'  => [
                    'AP' => 'ap',
                    'Hacker' => 'hacker',
                    'Builder' => 'builder',
                    'Trekker' => 'trekker',
                    'Purifier' => 'purifier',
                ],
            ])
            ->add('date_start')
            ->add('date_end')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
