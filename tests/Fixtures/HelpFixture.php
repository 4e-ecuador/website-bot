<?php

namespace App\Tests\Fixtures;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Entity\Comment;
use App\Entity\Faction;
use App\Entity\Help;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class HelpFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $agentRepo = $manager->getRepository(Agent::class);
        //
        // $agent = $agentRepo->find(1);
        //
        // $user = new User();
        // $user->setUsername('test');
        // $manager->persist($user);

        $help = new Help();

        $help->setText('test')
        ->setSlug('test')
        ->setTitle('Test');

        // $help->setAgent($agent);
        // $help->setCommenter($user)
        // ->setText('comment')
        // ->setDatetime(new \DateTime('now'))
        // // ->setAp(666)
        // // ->setExplorer(666)
        // // ->setRecon(666)
        // ;


        $manager->persist($help);

        $manager->flush();
    }
}
