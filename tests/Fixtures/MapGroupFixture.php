<?php

namespace App\Tests\Fixtures;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Entity\Comment;
use App\Entity\Faction;
use App\Entity\Help;
use App\Entity\MapGroup;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class MapGroupFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $object = new MapGroup();

        $object->setName('test');

        $manager->persist($object);

        $manager->flush();
    }
}
