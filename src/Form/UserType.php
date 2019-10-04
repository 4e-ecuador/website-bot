<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('username')
            ->add('email')
            //            ->add('password', PasswordType::class)
            ->add(
                'roles',
                ChoiceType::class,
                [
                    'choices'  => [
                        'Admin'  => 'ROLE_ADMIN',
                        'Editor' => 'ROLE_EDITOR',
                        'Agent'  => 'ROLE_AGENT',
                        // 'User'   => 'ROLE_USER',
                    ],
                    //                    'expanded' => true,
                    'multiple' => true,
                ]
            )
            ->add('agent');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
            ]
        );
    }
}
