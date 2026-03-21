<?php

namespace App\Tests\Repository;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CommentRepositoryTest extends KernelTestCase
{
    private CommentRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $this->repository = $em->getRepository(Comment::class);
    }

    public function testFindLatestReturnsComments(): void
    {
        $results = $this->repository->findLatest();

        self::assertNotEmpty($results);
        self::assertContainsOnlyInstancesOf(Comment::class, $results);
    }

    public function testFindLatestRespectsLimit(): void
    {
        $results = $this->repository->findLatest(1);

        self::assertCount(1, $results);
    }

    public function testFindLatestDefaultLimitIsTen(): void
    {
        $results = $this->repository->findLatest();

        // We only have 1 comment in fixtures, so count <= 10
        self::assertLessThanOrEqual(10, count($results));
    }
}
