<?php

namespace App\Tests\Service;

use App\Entity\Challenge;
use App\Entity\Event;
use App\Service\EventHelper;
use Doctrine\ORM\EntityManager;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use UnexpectedValueException;

class EventHelperChallengeSpanTest extends KernelTestCase
{
    use RecreateDatabaseTrait;

    private EventHelper $eventHelper;
    private EntityManager $em;

    public function setUp(): void
    {
        self::bootKernel();
        $this->em = self::$container->get('doctrine.orm.entity_manager');

        $challenge = (new Challenge())
            ->setName('TestPast')
            ->setDateStart((new \DateTime('now'))->modify('-1 day'))
            ->setDateEnd((new \DateTime('now'))->modify('-1 day'));
        $this->em->persist($challenge);

        $challenge = (new Challenge())
            ->setName('TestPresent')
            ->setDateStart(new \DateTime('now'))
            ->setDateEnd(new \DateTime('now'));
        $this->em->persist($challenge);

        $this->em->flush();

        $this->eventHelper = new EventHelper(
            $this->em->getRepository(Event::class),
            $this->em->getRepository(Challenge::class),
            'UTC'
        );
    }

    public function testGetInSpanInvalid(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(
            'Unknown span (must be: past, present or future)'
        );
        $this->eventHelper->getChallengesInSpan('test');
    }

    public function testGetInSpanPast(): void
    {
        $result = $this->eventHelper->getChallengesInSpan('past');

        self::assertIsArray($result);
        self::assertCount(1, $result);
    }

    public function testGetEventsInSpanPresent(): void
    {
        $result = $this->eventHelper->getChallengesInSpan('present');

        self::assertIsArray($result);
        self::assertCount(1, $result);
    }

    public function testGetInSpanFuture(): void
    {
        $result = $this->eventHelper->getChallengesInSpan('future');

        self::assertIsArray($result);
        self::assertCount(1, $result);
    }
}
