<?php


namespace App\Service;


use App\Entity\Agent;
use Symfony\Component\Filesystem\Filesystem;

class Templater
{

    /**
     * @var string
     */
    private $rootDir;
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(string $rootDir, Filesystem $filesystem)
    {

        $this->rootDir    = $rootDir;
        $this->filesystem = $filesystem;
    }

    public function getTemplate(string $name)
    {
        $path = $this->rootDir.'/text-files/'.$name;

        if ($this->filesystem->exists($path)) {
            return file_get_contents($path);
        }

        return ' file not found';
    }

    public function replaceAgentTemplate(string $templateName, Agent $agent)
    {
        $template = $this->getTemplate($templateName);

        $replacements = [
            '{agent_nick}' => $agent->getNickname(),
            '{agent_name}' => $agent->getRealName(),
            '{faction}'    => $agent->getFaction()->getName(),
            '{lat}'        => $agent->getLat(),
            '{lon}'        => $agent->getLon(),
        ];

        foreach ($replacements as $search => $replacement) {
            $template = str_replace($search, $replacement, $template);
        }

        return $template;
    }

}
