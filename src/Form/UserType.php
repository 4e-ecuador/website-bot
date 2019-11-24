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
    /**
     * @var AgentRepository
     */
    private $agentRepository;

    public function __construct(AgentRepository $agentRepository)
    {
        $this->agentRepository = $agentRepository;
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('username')
            ->add('email')
            // ->add('password', PasswordType::class)
            ->add(
                'roles',
                ChoiceType::class,
                [
                    'choices'  => [
                        'Admin'  => 'ROLE_ADMIN',
                        'Editor' => 'ROLE_EDITOR',
                        'Agent'  => 'ROLE_AGENT',
                    ],
                    'multiple' => true,
                ]
            )
            ->add(
                'agent', EntityType::class, [
                'class'       => Agent::class,
                // 'choice_label' => function(Agent $user) {
                //     return sprintf('(%d) %s', $user->getId(), $user->getNickname());
                // },
                'placeholder' => '',
                'choices'     => $this->agentRepository->findAllAlphabetical(),
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
