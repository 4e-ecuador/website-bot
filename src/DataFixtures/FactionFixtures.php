<?php

namespace App\DataFixtures;

use App\Entity\Faction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class FactionFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $factions = ['ENL', 'RES'];

        foreach ($factions as $f) {
            $faction = new Faction();

            $faction->setName($f);

            $manager->persist($faction);
        }

        $manager->flush();
    }
}
