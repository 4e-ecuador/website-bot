<?php

namespace App\Service;

use App\Repository\AgentRepository;
use Doctrine\ORM\NonUniqueResultException;
use DOMDocument;
use Michelf\MarkdownExtra;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MarkdownParser  extends MarkdownExtra
{
    public function __construct(
        private AgentRepository $agentRepository,
        private UrlGeneratorInterface $urlGenerator
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
        $text = $this->makeImagesResponsive($text);

        return $text;
    }

    /**
     * @throws NonUniqueResultException
     */
    private function replaceAgentName($text): string
    {
        $text = preg_replace_callback(
            '/@([a-zA-Z0-9]+)/',
            function ($agentName) {
                $agent = $this->agentRepository->findOneByNickName(
                    $agentName[1]
                );

                if (!$agent) {
                    return '<code>'.$agentName[0].'</code>';
                }

                $url = $this->urlGenerator->generate(
                    'agent_show',
                    array('id' => $agent->getId())
                );

                $linkText = sprintf(
                    '<img src="/build/images/logos/%s.svg" style="height: 32px" alt="logo"/> %s',
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

        return $text;
    }

    private function makeImagesResponsive(string $text): string
    {
        $testString = "<div>$text</div>";
        $testString = str_replace('<br>', '<br/>', $testString);

        $doc = new DOMDocument('1.0', 'UTF-8');
        // $doc->strictErrorChecking = true;
        // $doc->standalone = true;
        $doc->xmlStandalone = true;
        // $doc->formatOutput = true;
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
