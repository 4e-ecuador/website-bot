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
}
