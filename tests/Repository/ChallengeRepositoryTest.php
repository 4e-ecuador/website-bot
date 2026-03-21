<?php

namespace App\Tests\Repository;

use App\Entity\Challenge;
use App\Repository\ChallengeRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ChallengeRepositoryTest extends KernelTestCase
{
    private ChallengeRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $this->repository = $em->getRepository(Challenge::class);
    }

    public function testFindCurrentReturnsActiveChallenges(): void
    {
        // Fixture has 'TestPresent' with date_start=now, date_end=now
        $results = $this->repository->findCurrent();

        self::assertNotEmpty($results);
        self::assertContainsOnlyInstancesOf(Challenge::class, $results);

        $names = array_map(
            static fn (Challenge $c) => $c->getName(),
            $results
        );
        self::assertContains('TestPresent', $names);
    }

    public function testFindCurrentExcludesPastChallenges(): void
    {
        $results = $this->repository->findCurrent();

        $names = array_map(
            static fn (Challenge $c) => $c->getName(),
            $results
        );
        self::assertNotContains('TestPast', $names);
    }
}
