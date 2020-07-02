<?php

namespace App\Tests\Fixtures;

use App\Entity\Agent;
use App\Entity\Faction;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AgentUserFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faction = (new Faction())
            ->setName('fooTest');

        $manager->persist($faction);

        $agent = (new Agent())
            ->setNickname('UserAgent')
            ->setFaction($faction);

        $manager->persist($agent);

        $user = (new User())
            ->setEmail('t0kent3st@example.com')
            ->setAgent($agent)
            ->setRoles(['ROLE_AGENT'])
            ->setApiToken('T3stT0ken');

        $manager->persist($user);

        $manager->flush();
    }
}
