<?php

namespace App\Service;

use App\Repository\AgentRepository;
use Doctrine\ORM\NonUniqueResultException;
use DOMDocument;
use Michelf\MarkdownExtra;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MarkdownParser extends MarkdownExtra
{
    public function __construct(
        private readonly AgentRepository $agentRepository,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function transform($text): string
    {
        $text = parent::transform($text);

        $text = $this->replaceAgentName($text);

        return $this->makeImagesResponsive($text);
    }

    /**
     * @throws NonUniqueResultException
     */
    private function replaceAgentName(string $text): string
    {
        return preg_replace_callback(
            '/@([a-zA-Z0-9]+)/',
            function ($agentName) {
                $agent = $this->agentRepository->findOneByNickName(
                    $agentName[1]
                );

                if (!$agent instanceof \App\Entity\Agent) {
                    return '<code>'.$agentName[0].'</code>';
                }

                $url = $this->urlGenerator->generate(
                    'agent_show',
                    ['id' => $agent->getId()]
                );

                $linkText = sprintf(
                    '<img src="/images/logos/%s.svg" style="height: 32px" alt="logo"/> %s',
                    $agent->getFaction()->getName(),
                    $agentName[0]
                );

                return sprintf(
                    '<a href="%s" class="%s">%s</a>',
                    $url,
                    $agent->getFaction()->getName(),
                    $linkText
                );
            },
            $text
        );
    }

    private function makeImagesResponsive(string $text): string
    {
        $testString = sprintf('<div>%s</div>', $text);
        $testString = str_replace('<br>', '<br/>', $testString);

        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->xmlStandalone = true;
        $doc->loadXML($testString, LIBXML_NOWARNING | LIBXML_NOERROR);

        $sNode = $doc->getElementsByTagName('img');

        foreach ($sNode as $searchNode) {
            $searchNode->setAttribute('class', 'img-fluid');
            $doc->importNode($searchNode);
        }

        $result = $doc->saveHTML();

        return $result ?: $text;
    }
}
