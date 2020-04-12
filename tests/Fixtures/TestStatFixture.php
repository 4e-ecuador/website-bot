<?php

namespace App\Tests\Fixtures;

use App\Entity\TestStat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TestStatFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $object = new TestStat();

        $object->setCsv('test');

        $manager->persist($object);
        $manager->flush();
    }
}
