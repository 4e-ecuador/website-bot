<?php

namespace App\Tests\Fixtures;

use App\Entity\Agent;
use App\Entity\AgentStat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AgentStatFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $agentRepo = $manager->getRepository(Agent::class);

        $agent = $agentRepo->find(1);
        $stat = new AgentStat();

        $stat->setAgent($agent);
        $stat->setDatetime(new \DateTime('now'))
            ->setAp(666)
            ->setExplorer(666)
            ->setRecon(666);

        $manager->persist($stat);

        $manager->flush();
    }
}
