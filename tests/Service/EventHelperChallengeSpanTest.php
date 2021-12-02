<?php

namespace App\Tests\Service;

use App\Entity\Challenge;
use App\Entity\Event;
use App\Service\EventHelper;
use DateTime;
use Exception;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use UnexpectedValueException;

class EventHelperChallengeSpanTest extends KernelTestCase
{
    use RecreateDatabaseTrait;

    private EventHelper $eventHelper;

    public function setUp(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $tz = new \DateTimeZone('UTC');

        $challenge = (new Challenge())
            ->setName('TestPast')
            ->setDateStart((new DateTime('now', $tz))->modify('-1 day'))
            ->setDateEnd((new DateTime('now', $tz))->modify('-1 day'));
        $em->persist($challenge);

        $challenge = (new Challenge())
            ->setName('TestPresent')
            ->setDateStart(new DateTime('now', $tz))
            ->setDateEnd(new DateTime('now', $tz));
        $em->persist($challenge);

        $em->flush();

        $this->eventHelper = new EventHelper(
            $em->getRepository(Event::class),
            $em->getRepository(Challenge::class),
            'UTC'
        );
    }

    /**
     * @throws Exception
     */
    public function testGetInSpanInvalid(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(
            'Unknown span (must be: past, present or future)'
        );
        $this->eventHelper->getChallengesInSpan('test');
    }

    /**
     * @throws Exception
     */
    public function testGetInSpanPast(): void
    {
        $result = $this->eventHelper->getChallengesInSpan('past');

        self::assertIsArray($result);
        self::assertCount(1, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetEventsInSpanPresent(): void
    {
        $result = $this->eventHelper->getChallengesInSpan('present');

        self::assertIsArray($result);
        self::assertCount(1, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetInSpanFuture(): void
    {
        $result = $this->eventHelper->getChallengesInSpan('future');

        self::assertIsArray($result);
        self::assertCount(1, $result);
    }
}
