<?php

namespace App\Tests\Service;

use App\Entity\Agent;
use App\Entity\Faction;
use App\Service\Templater;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class TemplaterTest extends TestCase
{
    private string $tmpDir;

    private Templater $templater;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/templater_test_' . uniqid();
        mkdir($this->tmpDir . '/text-files', 0o777, true);

        $this->templater = new Templater($this->tmpDir, new Filesystem());
    }

    protected function tearDown(): void
    {
        $files = glob($this->tmpDir . '/text-files/*');
        if ($files) {
            array_map(unlink(...), $files);
        }

        @rmdir($this->tmpDir . '/text-files');
        @rmdir($this->tmpDir);
    }

    public function testGetTemplateReturnsFileContents(): void
    {
        file_put_contents($this->tmpDir . '/text-files/welcome.txt', 'Hello {agent_nick}!');

        $result = $this->templater->getTemplate('welcome.txt');

        self::assertSame('Hello {agent_nick}!', $result);
    }

    public function testGetTemplateReturnsNotFoundForMissingFile(): void
    {
        $result = $this->templater->getTemplate('nonexistent.txt');

        self::assertSame('File not found', $result);
    }

    public function testReplaceAgentTemplateBasic(): void
    {
        file_put_contents(
            $this->tmpDir . '/text-files/test.txt',
            'Welcome {agent_nick} ({agent_name}) of {faction}!'
        );

        $faction = new Faction();
        $faction->setName('Enlightened');

        $agent = new Agent();
        $agent->setNickname('TestAgent')
            ->setRealName('John Doe')
            ->setFaction($faction);

        $result = $this->templater->replaceAgentTemplate('test.txt', $agent);

        self::assertSame('Welcome TestAgent (John Doe) of Enlightened!', $result);
    }

    public function testReplaceAgentTemplateWithCoordinates(): void
    {
        file_put_contents(
            $this->tmpDir . '/text-files/location.txt',
            'Lat: {lat} Lon: {lon} {gmap_link} {intel_link} {osm_link}'
        );

        $faction = new Faction();
        $faction->setName('Resistance');

        $agent = new Agent();
        $agent->setNickname('GeoAgent')
            ->setFaction($faction)
            ->setLat('-0.180653')
            ->setLon('-78.467834');

        $result = $this->templater->replaceAgentTemplate('location.txt', $agent);

        self::assertStringContainsString('Lat: -0.180653', $result);
        self::assertStringContainsString('Lon: -78.467834', $result);
        self::assertStringContainsString('google.com/maps', $result);
        self::assertStringContainsString('intel.ingress.com', $result);
        self::assertStringContainsString('openstreetmap.org', $result);
    }

    public function testReplaceAgentTemplateWithoutCoordinates(): void
    {
        file_put_contents(
            $this->tmpDir . '/text-files/noloc.txt',
            '{lat} {gmap_link} {intel_link} {osm_link}'
        );

        $faction = new Faction();
        $faction->setName('Enlightened');

        $agent = new Agent();
        $agent->setNickname('NoLocAgent')
            ->setFaction($faction);

        $result = $this->templater->replaceAgentTemplate('noloc.txt', $agent);

        self::assertStringContainsString('Desconocida', $result);
        self::assertStringNotContainsString('google.com/maps', $result);
    }

    public function testReplaceAgentTemplateWithoutRealName(): void
    {
        file_put_contents(
            $this->tmpDir . '/text-files/name.txt',
            '{agent_name}'
        );

        $faction = new Faction();
        $faction->setName('Enlightened');

        $agent = new Agent();
        $agent->setNickname('Anon')
            ->setRealName('')
            ->setFaction($faction);

        $result = $this->templater->replaceAgentTemplate('name.txt', $agent);

        self::assertSame('Desconocido', $result);
    }
}
