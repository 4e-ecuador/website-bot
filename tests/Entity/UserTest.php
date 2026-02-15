<?php

namespace App\Tests\Entity;

use App\Entity\Agent;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $user = new User();

        self::assertNull($user->getId());
        self::assertSame('', $user->getEmail());
        self::assertNull($user->getAgent());
        self::assertNull($user->getGoogleId());
        self::assertNull($user->getFireBaseToken());
        self::assertSame('', $user->getApiToken());
        self::assertNull($user->getAvatar());
        self::assertNull($user->getAvatarEncoded());
        self::assertNull($user->getPassword());
        self::assertNull($user->getSalt());
    }

    public function testRolesAlwaysIncludeRoleUser(): void
    {
        $user = new User();

        self::assertContains('ROLE_USER', $user->getRoles());
    }

    public function testSetRoles(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $roles = $user->getRoles();

        self::assertContains('ROLE_ADMIN', $roles);
        self::assertContains('ROLE_USER', $roles);
    }

    public function testRolesAreUnique(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER', 'ROLE_USER', 'ROLE_ADMIN']);

        $roles = $user->getRoles();

        self::assertCount(2, $roles);
    }

    public function testHasRoleReturnsRoles(): void
    {
        $user = new User();

        self::assertSame($user->getRoles(), $user->hasRole());
    }

    public function testSettersAndGetters(): void
    {
        $user = new User();
        $agent = new Agent();
        $agent->setNickname('TestAgent');

        $user->setEmail('test@example.com')
            ->setAgent($agent)
            ->setGoogleId('google123')
            ->setFireBaseToken('firebase-token')
            ->setApiToken('api-token')
            ->setAvatar('avatar.jpg')
            ->setAvatarEncoded('base64data');

        self::assertSame('test@example.com', $user->getEmail());
        self::assertSame($agent, $user->getAgent());
        self::assertSame('google123', $user->getGoogleId());
        self::assertSame('firebase-token', $user->getFireBaseToken());
        self::assertSame('api-token', $user->getApiToken());
        self::assertSame('avatar.jpg', $user->getAvatar());
        self::assertSame('base64data', $user->getAvatarEncoded());
    }

    public function testGetUsernameReturnsEmail(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        self::assertSame('test@example.com', $user->getUsername());
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        self::assertSame('test@example.com', $user->getUserIdentifier());
    }

    public function testToStringReturnsEmail(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        self::assertSame('test@example.com', (string) $user);
    }

    public function testGetUserAgentNameWithAgent(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $agent = new Agent();
        $agent->setNickname('TestAgent');

        $user->setAgent($agent);

        self::assertSame('TestAgent <test@example.com>', $user->getUserAgentName());
    }

    public function testGetUserAgentNameWithoutAgent(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        self::assertSame('test@example.com', $user->getUserAgentName());
    }

    public function testEraseCredentials(): void
    {
        $user = new User();

        // Should not throw
        $user->eraseCredentials();

        self::assertNull($user->getPassword());
    }
}
