<?php

namespace App\DataFixtures;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Entity\Challenge;
use App\Entity\Comment;
use App\Entity\Event;
use App\Entity\Faction;
use App\Entity\Help;
use App\Entity\IngressEvent;
use App\Entity\MapGroup;
use App\Entity\TestStat;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = (new User())
            ->setEmail('admin@example.com')
            ->setRoles(['ROLE_ADMIN']);

        $manager->persist($user);

        $factionEnl = (new Faction())
        ->setName('ENL');

        $manager->persist($factionEnl);

        $agent = (new Agent())
        ->setFaction($factionEnl)
        ->setNickname('testAgent');


        $manager->persist($agent);

        $event = (new Event())
        ->setName('test')
        ->setDateStart(new \DateTime())
        ->setDateEnd(new \DateTime());

        $manager->persist($event);

        $mapGroup = (new MapGroup())
        ->setName('test');

        $manager->persist($mapGroup);

        $testStat = (new TestStat())
        ->setCsv('csvString');

        $manager->persist($testStat);

        $agentStat = (new AgentStat())
            ->setDatetime(new \DateTime())
        ->setAgent($agent)
        ->setAp(123);

        $manager->persist($agentStat);

        $ingressEvent = (new IngressEvent())
            ->setDateStart(new \DateTime())
            ->setDateEnd(new \DateTime())
        ;

        $manager->persist($ingressEvent);

        $tz = new \DateTimeZone('UTC');

        $challenge = (new Challenge())
            ->setName('TestPast')
            ->setDateStart((new DateTime('now', $tz))->modify('-1 day'))
            ->setDateEnd((new DateTime('now', $tz))->modify('-1 day'));
        $manager->persist($challenge);

        $challenge = (new Challenge())
            ->setName('TestPresent')
            ->setDateStart(new DateTime('now', $tz))
            ->setDateEnd(new DateTime('now', $tz));
        $manager->persist($challenge);

        $comment = (new Comment())
        ->setText('test')
        ->setAgent($agent)
        ->setCommenter($user)
        ->setDatetime(new \DateTime());

        $manager->persist($comment);

        $manager->persist(
            (new Help())
        );

        $manager->flush();
    }
}
