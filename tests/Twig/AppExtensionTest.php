<?php

namespace App\Tests\Twig;

use App\Service\IntlDateHelper;
use App\Service\MarkdownHelper;
use App\Service\MedalChecker;
use App\Twig\AppExtension;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class AppExtensionTest extends TestCase
{
    private AppExtension $extension;

    private MedalChecker&\PHPUnit\Framework\MockObject\Stub $medalChecker;

    protected function setUp(): void
    {
        $this->medalChecker = $this->createStub(MedalChecker::class);
        $markdownHelper = $this->createStub(MarkdownHelper::class);
        $intlDateHelper = $this->createStub(IntlDateHelper::class);

        $this->extension = new AppExtension(
            $this->medalChecker,
            $markdownHelper,
            $intlDateHelper,
            'America/Guayaquil',
        );
    }

    public function testGetFiltersReturnsArray(): void
    {
        $filters = $this->extension->getFilters();

        self::assertNotEmpty($filters);

        $names = array_map(static fn($f) => $f->getName(), $filters);

        self::assertContains('stripGmail', $names);
        self::assertContains('ucfirst', $names);
        self::assertContains('displayRoles', $names);
        self::assertContains('escape_bytecode', $names);
    }

    public function testGetFunctionsReturnsArray(): void
    {
        $functions = $this->extension->getFunctions();

        self::assertNotEmpty($functions);

        $names = array_map(static fn($f) => $f->getName(), $functions);

        self::assertContains('defaultTimeZone', $names);
        self::assertContains('getBadgeName', $names);
    }

    public function testStripGmail(): void
    {
        self::assertSame('user', $this->extension->stripGmail('user@gmail.com'));
        self::assertSame('user@yahoo.com', $this->extension->stripGmail('user@yahoo.com'));
    }

    public function testEscapeBytecode(): void
    {
        self::assertSame('\\xF0\\x9F', $this->extension->escapeBytecode('%F0%9F'));
    }

    public function testDisplayUcFirst(): void
    {
        self::assertSame('Hello', $this->extension->displayUcFirst('hello'));
        self::assertSame('Already', $this->extension->displayUcFirst('Already'));
    }

    public function testDisplayRolesFilter(): void
    {
        $roles = ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_AGENT'];

        $result = $this->extension->displayRolesFilter($roles);

        self::assertSame('Admin, Agent', $result);
    }

    public function testDisplayRolesFilterUnknownRole(): void
    {
        $roles = ['ROLE_USER', 'ROLE_UNKNOWN'];

        $result = $this->extension->displayRolesFilter($roles);

        self::assertSame('ROLE_UNKNOWN', $result);
    }

    public function testDisplayRolesFilterEmpty(): void
    {
        self::assertSame('', $this->extension->displayRolesFilter(['ROLE_USER']));
    }

    public function testGetBadgeNameAnomaly(): void
    {
        self::assertSame('anomaly_cassandra_1', $this->extension->getBadgeName('anomaly', 'cassandra', 1));
    }

    public function testGetBadgeNameAnomalyZeroValue(): void
    {
        self::assertSame('anomaly_cassandra', $this->extension->getBadgeName('anomaly', 'cassandra', 0));
    }

    public function testGetBadgeNameEvent(): void
    {
        self::assertSame('event_badge_fs_2', $this->extension->getBadgeName('event', 'fs', 2));
    }

    public function testGetBadgeNameAnnual(): void
    {
        $this->medalChecker->method('getMedalLevelName')->willReturn('gold');

        self::assertSame('badge_anniversary_gold', $this->extension->getBadgeName('annual', 'anniversary', 3));
    }

    public function testGetBadgeNameUnknownGroupThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Unknown group: invalid');

        $this->extension->getBadgeName('invalid', 'badge', 1);
    }

    public function testObjectFilter(): void
    {
        $obj = new \stdClass();
        $obj->name = 'test';
        $obj->value = 42;

        $result = $this->extension->objectFilter($obj);

        self::assertArrayHasKey('name', $result);
        self::assertSame('test', $result['name']);
        self::assertSame(42, $result['value']);
    }

    public function testGetDefaultTimeZone(): void
    {
        self::assertSame('America/Guayaquil', $this->extension->getDefaultTimeZone());
    }
}
