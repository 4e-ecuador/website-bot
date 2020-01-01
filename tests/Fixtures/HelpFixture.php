<?php

namespace App\Tests\Fixtures;

use App\Entity\Help;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class HelpFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $help = new Help();

        $help->setText('test')
            ->setSlug('test')
            ->setTitle('Test');

        $manager->persist($help);

        $manager->flush();
    }
}
