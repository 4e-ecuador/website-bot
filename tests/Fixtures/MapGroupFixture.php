<?php

namespace App\Tests\Fixtures;

use App\Entity\MapGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MapGroupFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $object = new MapGroup();

        $object->setName('TestMapGroup');

        $manager->persist($object);

        $manager->flush();
    }
}
