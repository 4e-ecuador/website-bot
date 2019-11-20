<?php

namespace App\Tests\Fixtures;

use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class EventFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $event = new Event();
        $event->setDateStart(new \DateTime());
        $event->setDateEnd(new \DateTime());
        $event->setName('TEST');

        $manager->persist($event);

        $manager->flush();
    }

}
