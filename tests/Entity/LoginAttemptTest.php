<?php

namespace App\Tests\Entity;

use App\Entity\LoginAttempt;
use PHPUnit\Framework\TestCase;

class LoginAttemptTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $attempt = new LoginAttempt();

        self::assertNull($attempt->getId());
        self::assertNull($attempt->getEmail());
        self::assertFalse($attempt->isSuccess());
        self::assertNull($attempt->getIpAddress());
        self::assertSame('unknown', $attempt->getAuthMethod());
        self::assertInstanceOf(\DateTimeInterface::class, $attempt->getCreatedAt());
    }

    public function testSettersAndGetters(): void
    {
        $attempt = new LoginAttempt();

        $attempt->setEmail('user@example.com');
        self::assertSame('user@example.com', $attempt->getEmail());

        $attempt->setSuccess(true);
        self::assertTrue($attempt->isSuccess());

        $attempt->setIpAddress('192.168.1.1');
        self::assertSame('192.168.1.1', $attempt->getIpAddress());

        $attempt->setAuthMethod('google');
        self::assertSame('google', $attempt->getAuthMethod());
    }

    public function testFluentSetters(): void
    {
        $attempt = new LoginAttempt();

        $result = $attempt
            ->setEmail('test@test.com')
            ->setSuccess(true)
            ->setIpAddress('10.0.0.1')
            ->setAuthMethod('form');

        self::assertSame($attempt, $result);
    }

    public function testCreatedAtIsSetOnConstruction(): void
    {
        $before = new \DateTime();
        $attempt = new LoginAttempt();
        $after = new \DateTime();

        self::assertGreaterThanOrEqual($before, $attempt->getCreatedAt());
        self::assertLessThanOrEqual($after, $attempt->getCreatedAt());
    }
}
