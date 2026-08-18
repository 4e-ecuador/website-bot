<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * First real-browser test for the "import statistics" workflow.
 *
 * Unlike StatImportControllerTest (HTTP kernel only), this drives an actual
 * headless Chrome via Symfony Panther and saves a screenshot of each step to
 * var/screenshots/, so the flow can be inspected visually.
 */
class StatImportBrowserTest extends PantherTestCase
{
    private const SCREENSHOT_DIR = __DIR__.'/../../var/screenshots';

    private const CSV_FIXTURE = __DIR__.'/../test-stats.csv';

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
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $em->createQuery(
            'DELETE FROM App\Entity\AgentStat s WHERE s.datetime = :datetime'
        )->setParameter('datetime', $this->csvDatetime())->execute();
        parent::tearDown();
    }

    public function testImportStatsWorkflowThroughBrowser(): void
    {
        if (!is_dir(self::SCREENSHOT_DIR)) {
            mkdir(self::SCREENSHOT_DIR, recursive: true);
        }

        // readinessPath must avoid the "/" route: it's https-only (see
        // DefaultController::index), and a redirect there makes Panther's
        // plain-http readiness probe try (and fail) to reach https://127.0.0.1.
        $client = self::createPantherClient(['readinessPath' => '/login']);
        $this->logIn($client);

        $crawler = $client->request('GET', '/stats/stat-import');
        self::assertSelectorExists('textarea[name="csv"]');
        $client->takeScreenshot(self::SCREENSHOT_DIR.'/01-import-form.png');

        // The CSV payload is tab-separated: WebDriver's sendKeys() treats a
        // literal tab character as a real Tab keypress and jumps focus out
        // of the field instead of typing it, so the value is set directly
        // via JS instead of simulating keystrokes.
        $client->executeScript(
            'document.getElementById("csv").value = arguments[0];',
            [$this->csvContent()]
        );

        $crawler->filter('#nav-home button')->click();
        // Not asserting on the h2 text: the locale is negotiated per
        // request/session and varies with test order, so it can render
        // "Import result", "Resultado del importe", etc.
        $client->waitFor('.alert-success');

        self::assertSelectorExists('.alert-success');
        self::assertSelectorNotExists('.alert-danger');
        $client->takeScreenshot(self::SCREENSHOT_DIR.'/02-import-result.png');
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
        // Uses a static asset rather than a route: if a later test in this
        // class reuses the same (already-authenticated) browser session,
        // hitting a route like "/login" while already logged in redirects
        // to the "default" route - which is https-only and fails against
        // this plain-http dev server.
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

    private function csvContent(): string
    {
        return (string)file_get_contents(self::CSV_FIXTURE);
    }

    /**
     * Mirrors CsvParser::parsePrimeCsv()'s own date+time extraction, so the
     * cleanup query always matches whatever tests/test-stats.csv contains.
     */
    private function csvDatetime(): string
    {
        $lines = explode("\n", trim($this->csvContent()));
        $vars = explode("\t", $lines[1]);

        return $vars[3].' '.$vars[4];
    }
}
