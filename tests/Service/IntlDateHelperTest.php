<?php

namespace App\Tests\Service;

use App\Service\IntlDateHelper;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IntlDateHelperTest extends KernelTestCase
{
    private IntlDateHelper $intlDateHelper;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->intlDateHelper = new IntlDateHelper(
            'UTC', self::getContainer()->get('translator')
        );
    }

    public function testFormat(): void
    {
        $response = $this->intlDateHelper->format(
            new DateTime('2112-12-21T00:00:00+00:00')
        );

        self::assertEquals('21 December 2112', $response);
    }

    public function testFormatShort(): void
    {
        $response = $this->intlDateHelper->formatShort(
            new DateTime('2112-12-21T00:00:00+00:00')
        );

        self::assertEquals('21 December', $response);
    }

    public function testFormatCustom(): void
    {
        $response = $this->intlDateHelper->formatCustom(
            new DateTime('2112-12-21T00:00:00+00:00'),
            "dd 'de' MMMM 'xx' YYYY"
        );

        self::assertEquals('21 de December xx 2112', $response);
    }

    public function testGetTimeZone(): void
    {
        $response = $this->intlDateHelper->getDefaultTimezone();

        self::assertEquals('UTC', $response->getName());
    }

}
