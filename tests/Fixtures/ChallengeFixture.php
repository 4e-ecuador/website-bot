<?php

namespace App\Tests\Fixtures;

use App\Entity\Challenge;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ChallengeFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $object = new Challenge();
        $object->setDateStart(new \DateTime());
        $object->setDateEnd(new \DateTime());
        $object->setName('TEST');

        $manager->persist($object);

        $manager->flush();
    }
}
