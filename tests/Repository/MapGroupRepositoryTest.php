<?php

namespace App\Tests\Repository;

use App\Entity\MapGroup;
use App\Repository\MapGroupRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MapGroupRepositoryTest extends KernelTestCase
{
    private MapGroupRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $this->repository = $em->getRepository(MapGroup::class);
    }

    public function testGetNamesReturnsArray(): void
    {
        $results = $this->repository->getNames();

        self::assertNotEmpty($results);
        self::assertIsArray($results);
    }

    public function testGetNamesContainsFixtureName(): void
    {
        $results = $this->repository->getNames();

        $names = array_column($results, 'name');
        self::assertContains('test', $names);
    }
}
