<?php

namespace App\Tests\Service;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Entity\Challenge;
use App\Entity\Event;
use App\Service\EventHelper;
use Doctrine\ORM\EntityManager;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use UnexpectedValueException;

class EventHelperTest extends KernelTestCase
{
    use RecreateDatabaseTrait;

    private EventHelper $eventHelper;
    private EntityManager $em;

    public function setUp(): void
    {
        self::bootKernel();
        $this->em = self::$container->get('doctrine.orm.entity_manager');

        $this->eventHelper = new EventHelper(
            $this->em->getRepository(Event::class),
            $this->em->getRepository(Challenge::class),
            'UTC'
        );
    }

    public function testGetNextFs(): void
    {
        $result = $this->eventHelper->getNextFS();

        self::assertInstanceOf(\DateTime::class, $result);
    }

    public function testCalculateResultsInvalidType(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Unknown event type: test');

        $agent = (new Agent())
            ->setNickname('testAgent');
        $event = (new Event())
            ->setEventType('test');
        $entries = [
            (new AgentStat())
                ->setAgent($agent),
            (new AgentStat())
                ->setAgent($agent),
        ];
        $this->eventHelper->calculateResults($event, $entries);
    }

    public function testCalculateResults(): void
    {
        $agent = (new Agent())
            ->setNickname('testAgent');
        $event = (new Event())
            ->setEventType('explorer');
        $entries = [
            (new AgentStat())
                ->setAgent($agent),
            (new AgentStat())
                ->setAgent($agent)
                ->setExplorer(1),
        ];

        $result = $this->eventHelper->calculateResults($event, $entries);

        $expected = ['testAgent' => 1];
        self::assertIsArray($result);
        self::assertSame($expected, $result);
    }

    public function testCalculateResultsFieldsLinks(): void
    {
        $agent = (new Agent())
            ->setNickname('testAgent');
        $event = (new Event())
            ->setEventType('fieldslinks');
        $entries = [
            (new AgentStat())
                ->setAgent($agent),
            (new AgentStat())
                ->setAgent($agent)
                ->setMindController(3)
                ->setConnector(2),
        ];

        $result = $this->eventHelper->calculateResults($event, $entries);

        $expected = ['testAgent' => 1.5];
        self::assertIsArray($result);
        self::assertSame($expected, $result);
    }
}
