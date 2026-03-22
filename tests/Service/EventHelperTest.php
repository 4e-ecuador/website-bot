<?php

namespace App\Tests\Service;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Entity\Challenge;
use App\Entity\Event;
use App\Service\EventHelper;
use Exception;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use UnexpectedValueException;

class EventHelperTest extends KernelTestCase
{
    private EventHelper $eventHelper;

    protected function setUp(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get('doctrine.orm.entity_manager');

        $this->eventHelper = new EventHelper(
            $em->getRepository(Event::class),
            $em->getRepository(Challenge::class),
            'UTC'
        );
    }

    /**
     * @throws Exception
     */
    public function testGetNextFs(): void
    {
        $result = $this->eventHelper->getNextFS();

        self::assertSame('Saturday', $result->format('l'));
    }

    public function testCalculateResultsInvalidType(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Unknown event type: test');

        $agent = new Agent()->setNickname('testAgent');
        $event = new Event()->setEventType('test');
        $entries = [
            new AgentStat()->setAgent($agent),
            new AgentStat()->setAgent($agent),
        ];
        $this->eventHelper->calculateResults($event, $entries);
    }

    public function testCalculateResults(): void
    {
        $agent = new Agent()
            ->setNickname('testAgent');
        $event = new Event()
            ->setEventType('explorer');
        $entries = [
            new AgentStat()
                ->setAgent($agent),
            new AgentStat()
                ->setAgent($agent)
                ->setExplorer(1),
        ];

        $result = $this->eventHelper->calculateResults($event, $entries);

        $expected = ['testAgent' => 1];
        self::assertSame($expected, $result);
    }

    public function testCalculateResultsFieldsLinks(): void
    {
        $agent = new Agent()
            ->setNickname('testAgent');
        $event = new Event()
            ->setEventType('fieldslinks');
        $entries = [
            new AgentStat()
                ->setAgent($agent),
            new AgentStat()
                ->setAgent($agent)
                ->setMindController(3)
                ->setConnector(2),
        ];

        $result = $this->eventHelper->calculateResults($event, $entries);

        $expected = ['testAgent' => 1.5];
        self::assertSame($expected, $result);
    }

    public function testCalculateResultsFieldsLinksWithZeroLinks(): void
    {
        $agent = new Agent()
            ->setNickname('testAgent');
        $event = new Event()
            ->setEventType('fieldslinks');
        $entries = [
            new AgentStat()
                ->setAgent($agent),
            new AgentStat()
                ->setAgent($agent)
                ->setMindController(3),
                // Connector stays 0
        ];

        $result = $this->eventHelper->calculateResults($event, $entries);

        // When links = 0, the result should be 0 (avoid division by zero)
        $expected = ['testAgent' => 0];
        self::assertSame($expected, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetEventsInSpanReturnsPresent(): void
    {
        $events = $this->eventHelper->getEventsInSpan('present');

        self::assertNotNull($events);
        self::assertNotEmpty($events);
        self::assertContainsOnlyInstancesOf(Event::class, $events);
    }

    /**
     * @throws Exception
     */
    public function testGetEventsInSpanInvalidSpanThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $this->eventHelper->getEventsInSpan('invalid');
    }

    /**
     * @throws Exception
     */
    public function testGetChallengesInSpanReturnsPast(): void
    {
        $challenges = $this->eventHelper->getChallengesInSpan('past');

        self::assertNotNull($challenges);
        self::assertNotEmpty($challenges);
        self::assertContainsOnlyInstancesOf(Challenge::class, $challenges);
    }

    /**
     * @throws Exception
     */
    public function testGetChallengesInSpanReturnsPresent(): void
    {
        $challenges = $this->eventHelper->getChallengesInSpan('present');

        self::assertNotNull($challenges);
        self::assertNotEmpty($challenges);
        self::assertContainsOnlyInstancesOf(Challenge::class, $challenges);
    }

    /**
     * @throws Exception
     */
    public function testGetChallengesInSpanInvalidSpanThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $this->eventHelper->getChallengesInSpan('invalid');
    }
}
