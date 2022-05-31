<?php

namespace App\Service;

use App\Entity\Agent;
use Symfony\Component\Filesystem\Filesystem;

class Templater
{
    public function __construct(
        private readonly string $rootDir,
        private readonly Filesystem $filesystem
    ) {
    }

    public function getTemplate(string $templateName): string
    {
        $path = $this->rootDir.'/text-files/'.$templateName;

        if ($this->filesystem->exists($path)) {
            return (string)file_get_contents($path);
        }

        return 'File not found';
    }

    public function replaceAgentTemplate(
        string $templateName,
        Agent $agent
    ): string {
        $template = $this->getTemplate($templateName);

        $replacements = [
            '{agent_nick}' => $agent->getNickname(),
            '{agent_name}' => $agent->getRealName() ?: 'Desconocido',
            '{faction}'    => $agent->getFaction()->getName(),
            '{lat}'        => $agent->getLat() ?: 'Desconocida',
            '{lon}'        => $agent->getLon(),
            '{gmap_link}'  => $agent->getLat() ? sprintf(
                '[gmap](https://www.google.com/maps/@%s,%s,17z)',
                $agent->getLat(),
                $agent->getLon()
            ) : '',
            '{intel_link}' => $agent->getLat() ? sprintf(
                '[intel](https://intel.ingress.com/intel?ll=%s,%s&z=17)',
                $agent->getLat(),
                $agent->getLon()
            ) : '',
            '{osm_link}'   => $agent->getLat() ? sprintf(
                '[osm](https://www.openstreetmap.org/?mlat=%s&mlon=%s#map=10/%s/%s)',
                $agent->getLat(),
                $agent->getLon(),
                $agent->getLat(),
                $agent->getLon()
            ) : '',
        ];

        foreach ($replacements as $search => $replacement) {
            $template = str_replace($search, (string)$replacement, $template);
        }

        return $template;
    }

}
