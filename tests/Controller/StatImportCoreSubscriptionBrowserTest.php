<?php

namespace App\Tests\Controller;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Entity\User;
use DateTime;
use RuntimeException;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Browser-driven tests for the CORE subscription badges shown after
 * importing stats (see StatsImporter::checkCoreSubscription()). The result
 * page doesn't have dedicated copy for these ("first core subscribed" etc.)
 * -it renders a badge per StatsImporter\ImportResult::$coreSubscribed entry,
 * with the raw internal name ('core', 'dual_core', 'core_year3', 'quad_core',
 * 'penta_core') as both the CSS class suffix and the title attribute- so
 * that's what's asserted on and what shows up in the screenshots.
 *
 * The month-based tiers (24/36/48/60) are mutually exclusive: only the
 * highest tier crossed since the previous import fires, alongside 'core'
 * when this is the agent's first ever subscribed import.
 */
class StatImportCoreSubscriptionBrowserTest extends PantherTestCase
{
    private const SCREENSHOT_DIR = __DIR__.'/../../var/screenshots';

    private const CSV_FIXTURE = __DIR__.'/../test-stats.csv';

    /** @var array<int, string> */
    private array $seededDatetimes = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // When run via "symfony php/console" (as make tests does) while a
        // "symfony server:start" is running, Panther would otherwise adopt
        // that server as an "external" one instead of spawning its own -
        // pointing the browser at the real dev app/DB/session storage
        // under APP_ENV=dev rather than this isolated test run.
        unset($_SERVER['SYMFONY_PROJECT_DEFAULT_ROUTE_URL'], $_SERVER['PANTHER_EXTERNAL_BASE_URI']);
    }

    protected function tearDown(): void
    {
        if ($this->seededDatetimes !== []) {
            self::getContainer()->get('doctrine.orm.entity_manager')
                ->createQuery('DELETE FROM App\Entity\AgentStat s WHERE s.datetime IN (:dates)')
                ->setParameter('dates', $this->seededDatetimes)
                ->execute();
        }

        parent::tearDown();
    }

    public function testFirstCoreSubscriptionBadgeAppears(): void
    {
        $this->seedPreviousStat(new DateTime('2020-01-01 00:00:00'), null);

        $client = $this->import([
            'Date (yyyy-mm-dd)' => '2020-01-02',
            'Time (hh:mm:ss)'   => '00:00:00',
            'Months Subscribed' => '1',
        ]);

        $client->waitFor('.medal-unique_badge_core');
        $client->takeScreenshot(self::SCREENSHOT_DIR.'/core-01-first-subscribed.png');

        self::assertSelectorExists('.medal-unique_badge_core[title="core"]');
        self::assertSelectorNotExists('.medal-unique_badge_dual_core');
        self::assertSelectorNotExists('.medal-unique_badge_core_year3');
    }

    public function testDualCoreSubscriptionBadgeAppears(): void
    {
        $this->seedPreviousStat(new DateTime('2021-01-01 00:00:00'), 20);

        $client = $this->import([
            'Date (yyyy-mm-dd)' => '2021-01-02',
            'Time (hh:mm:ss)'   => '00:00:00',
            'Months Subscribed' => '24',
        ]);

        $client->waitFor('.medal-unique_badge_dual_core');
        $client->takeScreenshot(self::SCREENSHOT_DIR.'/core-02-dual-core.png');

        self::assertSelectorExists('.medal-unique_badge_dual_core[title="dual_core"]');
        // Already subscribed before this import, so the "first core" badge must not re-appear.
        self::assertSelectorNotExists('.medal-unique_badge_core[title="core"]');
        self::assertSelectorNotExists('.medal-unique_badge_core_year3');
    }

    public function testTripleCoreSubscriptionBadgeAppears(): void
    {
        $this->seedPreviousStat(new DateTime('2022-01-01 00:00:00'), 30);

        $client = $this->import([
            'Date (yyyy-mm-dd)' => '2022-01-02',
            'Time (hh:mm:ss)'   => '00:00:00',
            'Months Subscribed' => '36',
        ]);

        $client->waitFor('.medal-unique_badge_core_year3');
        $client->takeScreenshot(self::SCREENSHOT_DIR.'/core-03-triple-core.png');

        self::assertSelectorExists('.medal-unique_badge_core_year3[title="core_year3"]');
        // Tiers are mutually exclusive - only the highest one crossed fires.
        self::assertSelectorNotExists('.medal-unique_badge_dual_core');
        self::assertSelectorNotExists('.medal-unique_badge_core[title="core"]');
        self::assertSelectorNotExists('.medal-unique_badge_quad_core');
        self::assertSelectorNotExists('.medal-unique_badge_penta_core');
    }

    public function testQuadCoreSubscriptionBadgeAppears(): void
    {
        $this->seedPreviousStat(new DateTime('2023-01-01 00:00:00'), 40);

        $client = $this->import([
            'Date (yyyy-mm-dd)' => '2023-01-02',
            'Time (hh:mm:ss)'   => '00:00:00',
            'Months Subscribed' => '48',
        ]);

        $client->waitFor('.medal-unique_badge_quad_core');
        $client->takeScreenshot(self::SCREENSHOT_DIR.'/core-04-quad-core.png');

        self::assertSelectorExists('.medal-unique_badge_quad_core[title="quad_core"]');
        self::assertSelectorNotExists('.medal-unique_badge_core_year3');
        self::assertSelectorNotExists('.medal-unique_badge_dual_core');
        self::assertSelectorNotExists('.medal-unique_badge_penta_core');
    }

    public function testPentaCoreSubscriptionBadgeAppears(): void
    {
        $this->seedPreviousStat(new DateTime('2024-01-01 00:00:00'), 50);

        $client = $this->import([
            'Date (yyyy-mm-dd)' => '2024-01-02',
            'Time (hh:mm:ss)'   => '00:00:00',
            'Months Subscribed' => '60',
        ]);

        $client->waitFor('.medal-unique_badge_penta_core');
        $client->takeScreenshot(self::SCREENSHOT_DIR.'/core-05-penta-core.png');

        self::assertSelectorExists('.medal-unique_badge_penta_core[title="penta_core"]');
        self::assertSelectorNotExists('.medal-unique_badge_quad_core');
        self::assertSelectorNotExists('.medal-unique_badge_core_year3');
        self::assertSelectorNotExists('.medal-unique_badge_dual_core');
    }

    /**
     * Persists a minimal prior AgentStat directly (bypassing the browser) so
     * StatsImporter::getImportResult() has something to diff the CSV import
     * against - it looks up the agent's most recent AgentStat before the new
     * entry's datetime.
     */
    private function seedPreviousStat(DateTime $datetime, ?int $monthsSubscribed): void
    {
        $em = self::getContainer()->get('doctrine.orm.entity_manager');

        $stat = new AgentStat()
            ->setAgent($this->getFixtureAgent())
            ->setDatetime($datetime)
            ->setMonthsSubscribed($monthsSubscribed);

        $em->persist($stat);
        $em->flush();

        $this->seededDatetimes[] = $datetime->format('Y-m-d H:i:s');
    }

    /**
     * @param array<string, string> $columnOverrides Header name => new value
     */
    private function import(array $columnOverrides): Client
    {
        $client = self::createPantherClient(['readinessPath' => '/login']);
        $this->logIn($client);

        $csv = $this->csvWithOverrides($columnOverrides);
        [, $dataLine] = explode("\n", trim($csv));
        $fields = explode("\t", $dataLine);
        $this->seededDatetimes[] = $fields[3].' '.$fields[4];

        $crawler = $client->request('GET', '/stats/stat-import');
        $client->executeScript(
            'document.getElementById("csv").value = arguments[0];',
            [$csv]
        );
        $crawler->filter('#nav-home button')->click();

        return $client;
    }

    private function csvWithOverrides(array $columnOverrides): string
    {
        $lines = explode("\n", trim((string)file_get_contents(self::CSV_FIXTURE)));
        $header = explode("\t", $lines[0]);
        $data = explode("\t", $lines[1]);

        foreach ($columnOverrides as $column => $value) {
            $index = array_search($column, $header, true);
            if ($index === false) {
                throw new RuntimeException("Unknown CSV column: {$column}");
            }
            $data[$index] = $value;
        }

        return implode("\t", $header)."\n".implode("\t", $data);
    }

    private function getFixtureAgent(): Agent
    {
        /** @var User $user */
        $user = self::getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['email' => 'admin@example.com']);

        /** @var Agent $agent */
        $agent = $user->getAgent();

        return $agent;
    }

    /**
     * Logs the browser in by writing a security token straight into the
     * session store and handing the session cookie to the real browser -
     * the app's login form is dev-only, so this is the way to authenticate
     * a Panther session as described in the Symfony testing docs.
     */
    private function logIn(Client $client): void
    {
        // A page must be loaded before cookies can be set for its domain.
        // Uses a static asset rather than a route: across the three tests in
        // this class the same browser session is reused, so by the 2nd/3rd
        // test the browser is already authenticated from the previous test,
        // and hitting a route like "/login" while already logged in
        // redirects to the "default" route - which is https-only and
        // unreachable on this plain-http dev server.
        $client->request('GET', '/favicon.ico');

        $user = self::getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['email' => 'admin@example.com']);

        $session = self::getContainer()->get('session.factory')->createSession();
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $session->set('_security_main', serialize($token));
        $session->save();

        $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));
    }
}
