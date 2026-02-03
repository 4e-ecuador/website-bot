<?php

namespace App\Tests\Service;

use App\Entity\AgentStat;
use App\Service\MedalChecker;
use App\Util\BadgeData;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use UnexpectedValueException;

class MedalCheckerTest extends KernelTestCase
{
    private MedalChecker $medalChecker;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->medalChecker = new MedalChecker(
            self::getContainer()->get('translator'),
            $kernel->getProjectDir(),
            'test'
        );
    }

    public function testCheckLevels(): void
    {
        $statEntry = new AgentStat()
            ->setExplorer(100);
        $result = $this->medalChecker->checkLevels($statEntry);

        self::assertArrayHasKey('explorer', $result);
        self::assertEquals(1, $result['explorer']);
    }

    public function testGetLevelName(): void
    {
        $levels = [
            1 => 'bronze',
            2 => 'silver',
            3 => 'gold',
            4 => 'platinum',
            5 => 'black',
        ];
        foreach ($levels as $i => $level) {
            $result = $this->medalChecker->getLevelName($i);
            self::assertSame($level, $result);
        }
    }

    public function testTranslatePrimeHeader(): void
    {
        $result = $this->medalChecker->translatePrimeHeader('Agent Name');
        self::assertSame('nickname', $result);
    }

    public function testGetUpgrades(): void
    {
        $statEntry = new AgentStat();
        $statEntry2 = new AgentStat()
            ->setExplorer(100);
        $result = $this->medalChecker->getUpgrades($statEntry, $statEntry2);

        $expected = ['explorer' => 1];
        self::assertEquals($expected, $result);
    }

    public function testGetDescription(): void
    {
        $result = $this->medalChecker->getDescription('explorer');
        self::assertSame('Unique Portals Visited', $result);
    }

    public function testGetLevelValue(): void
    {
        $result = $this->medalChecker->getLevelValue('explorer', 1);
        self::assertSame(100, $result);
    }

    public function testGetMedalLevel(): void
    {
        $medals = [
            'explorer'       => 100,
            'nl1331Meetups'  => 1,
            'mindController' => 100,
        ];

        foreach ($medals as $medal => $value) {
            $result = $this->medalChecker->getMedalLevel($medal, $value);

            self::assertSame(
                1,
                $result,
                sprintf('Failed for %s -  %s', $medal, $value)
            );
        }

        $result = $this->medalChecker->getMedalLevel('XXX', 0);

        self::assertSame(0, $result);

        $levels = [
            1 => 100,
            2 => 1000,
            3 => 2000,
            4 => 10000,
            5 => 30000,
        ];
        foreach ($levels as $level => $value) {
            $result = $this->medalChecker->getMedalLevel('explorer', $value);
            self::assertSame($level, $result, $level.' - '.$result);
        }
    }

    public function testTranslateMedalLevel(): void
    {
        $result = $this->medalChecker->translateMedalLevel(1);
        self::assertSame('bronce', $result);
    }

    public function testGetDoubleValue(): void
    {
        $result = $this->medalChecker->getDoubleValue('explorer', 30000);
        self::assertSame(1, $result);
        $result = $this->medalChecker->getDoubleValue('explorer', 60000);
        self::assertSame(2, $result);
    }

    public function testGetBadgePath(): void
    {
        $badges = [
            'explorer'        => 'badge_explorer_bronze.png',
            'mind-controller' => 'badge_mind_controller_bronze.png',
            'Recon'           => 'badge_Recon_bronze.png',
        ];
        foreach ($badges as $badge => $image) {
            $result = $this->medalChecker->getBadgePath($badge, 1);
            self::assertSame($image, $result);
        }
    }

    public function testGetBadgePath2(): void
    {
        $result = $this->medalChecker->getBadgePath('explorer', 1, 666, '.xxx');
        self::assertSame('badge_explorer_bronze_666.xxx', $result);
    }

    public function testGetChallengePath(): void
    {
        $result = $this->medalChecker->getChallengePath('explorer', 1);
        self::assertSame('event_badge_explorer_bronze', $result);
    }

    public function testGetCustomMedalGroups(): void
    {
        $result = $this->medalChecker->getCustomMedalGroups();
        self::assertNotEmpty($result);
    }

    public function testGetMedalLevelNames(): void
    {
        $result = $this->medalChecker->getMedalLevelNames();
        self::assertNotEmpty($result);
    }

    public function testGetMedalLevelName(): void
    {
        $result = $this->medalChecker->getMedalLevelName(1);
        self::assertSame('bronze', $result);
    }

    public function testGetBadgeData(): void
    {
        $result = $this->medalChecker->getBadgeData('anomaly_kureze_effect');
        self::assertInstanceOf(BadgeData::class, $result);
        self::assertSame('Kureze Effect', $result->title);
        self::assertSame(
            'In recognition of contributions during Kureze Effect.',
            $result->description
        );
    }

    public function testGetBadgeDataException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('No data for code: TEST');
        $this->medalChecker->getBadgeData('TEST');
    }
}
