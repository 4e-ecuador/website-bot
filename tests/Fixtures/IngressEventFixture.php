<?php

namespace App\Tests\Fixtures;

use App\Entity\IngressEvent;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class IngressEventFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $event = new IngressEvent();
        $event->setDateStart(new \DateTime());
        $event->setDateEnd(new \DateTime());
        $event->setName('TEST');
        $event->setType('test');

        $manager->persist($event);

        $manager->flush();
    }

}
