<?php

namespace App\Form;

use App\Entity\Agent;
use App\Entity\User;
use App\Repository\AgentRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function __construct(
        private readonly AgentRepository $agentRepository
    ) {
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('email', null, ['disabled' => true])
            ->add(
                'agent',
                EntityType::class,
                [
                    'class'       => Agent::class,
                    'placeholder' => '',
                    'required'    => false,
                    'choices'     => $this->agentRepository->findAllAlphabetical(
                    ),
                ]
            )
            ->add(
                'roles',
                ChoiceType::class,
                [
                    'choices'  => [
                        'Admin'       => 'ROLE_ADMIN',
                        'Editor'      => 'ROLE_EDITOR',
                        'Agent'       => 'ROLE_AGENT',
                        'Intro Agent' => 'ROLE_INTRO_AGENT',
                        'User'        => 'ROLE_USER',
                    ],
                    'multiple' => true,
                ]
            );
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
