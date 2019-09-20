<?php

namespace App\Form;

use App\Entity\AgentStat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgentStatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('datetime')
            ->add('ap')
            ->add('explorer')
            ->add('recon')
            ->add('seer')
            ->add('trekker')
            ->add('builder')
            ->add('connector')
            ->add('mindController')
            ->add('illuminator')
            ->add('recharger')
            ->add('liberator')
            ->add('pioneer')
            ->add('engineer')
            ->add('purifier')
            ->add('specops')
            ->add('hacker')
            ->add('translator')
            ->add('sojourner')
            ->add('recruiter')
            ->add('missionday')
            ->add('nl1331Meetups')
            ->add('ifs')
            ->add('agent');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => AgentStat::class,
            ]
        );
    }
}
