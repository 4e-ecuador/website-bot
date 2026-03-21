<?php

namespace App\Tests\Repository;

use App\Entity\Agent;
use App\Entity\Faction;
use App\Entity\User;
use App\Helper\Paginator\PaginatorOptions;
use App\Repository\UserRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private UserRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $this->repository = $em->getRepository(User::class);
    }

    public function testGetFireBaseUsersReturnsEmptyByDefault(): void
    {
        // Fixture user has no Firebase token
        $results = $this->repository->getFireBaseUsers();

        self::assertEmpty($results);
    }

    public function testGetFireBaseUsersFindsUserWithToken(): void
    {
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $email = 'firebase-' . uniqid() . '@example.com';

        $user = new User();
        $user->setEmail($email);
        $user->setFireBaseToken('test-token-123');

        $em->persist($user);
        $em->flush();

        $results = $this->repository->getFireBaseUsers();

        self::assertNotEmpty($results);
        $found = array_any($results, fn($u) => $u->getFireBaseToken() !== null);

        self::assertTrue($found);
    }

    public function testFindByAgentReturnsNullForUnlinkedAgent(): void
    {
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $faction = $em->getRepository(Faction::class)->findOneBy([]);

        $agent = new Agent();
        $agent->setNickname('unlinked-' . uniqid());
        $agent->setFaction($faction);

        $em->persist($agent);
        $em->flush();

        $result = $this->repository->findByAgent($agent);

        self::assertNull($result);
    }

    public function testFindByAgentReturnsUserWhenLinked(): void
    {
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $faction = $em->getRepository(Faction::class)->findOneBy([]);

        $agent = new Agent();
        $agent->setNickname('linked-' . uniqid());
        $agent->setFaction($faction);

        $em->persist($agent);

        $email = 'agentuser-' . uniqid() . '@example.com';
        $user = new User();
        $user->setEmail($email);
        $user->setAgent($agent);

        $em->persist($user);
        $em->flush();

        $result = $this->repository->findByAgent($agent);

        self::assertNotNull($result);
        self::assertSame($email, $result->getEmail());
    }

    public function testGetPaginatedListReturnsPaginator(): void
    {
        $options = new PaginatorOptions();
        $options->setOrder('id');
        $options->setOrderDir('ASC');
        $options->setPage(1);

        $paginator = $this->repository->getPaginatedList($options);

        self::assertInstanceOf(Paginator::class, $paginator);
        self::assertGreaterThan(0, count($paginator));
    }

    public function testGetPaginatedListWithEmailFilter(): void
    {
        $options = new PaginatorOptions();
        $options->setOrder('id');
        $options->setOrderDir('ASC');
        $options->setPage(1);
        $options->setCriteria(['email' => 'admin']);

        $paginator = $this->repository->getPaginatedList($options);

        self::assertInstanceOf(Paginator::class, $paginator);
        self::assertGreaterThan(0, count($paginator));

        foreach ($paginator as $user) {
            self::assertStringContainsStringIgnoringCase('admin', $user->getEmail());
        }
    }

    public function testGetPaginatedListWithRolesFilter(): void
    {
        $options = new PaginatorOptions();
        $options->setOrder('id');
        $options->setOrderDir('ASC');
        $options->setPage(1);
        $options->setCriteria(['roles' => 'ROLE_ADMIN']);

        // getPaginatedList returns a Paginator; the roles filter branch is exercised
        $paginator = $this->repository->getPaginatedList($options);

        self::assertInstanceOf(Paginator::class, $paginator);
    }
}
