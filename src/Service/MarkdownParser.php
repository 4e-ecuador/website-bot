<?php

namespace App\Service;

use App\Repository\AgentRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MarkdownParser extends \Knp\Bundle\MarkdownBundle\Parser\MarkdownParser
{
    /**
     * @var AgentRepository
     */
    private $agentRepository;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(AgentRepository $agentRepository, UrlGeneratorInterface $urlGenerator)
    {
        parent::__construct();
        $this->agentRepository = $agentRepository;
        $this->urlGenerator = $urlGenerator;
    }

    public function transform($text)
    {
        $text = parent::transform($text);

        $text = $this->replaceAgentName($text);

        return $text;
    }

    private function replaceAgentName($text): string
    {
        $text = preg_replace_callback(
            '/@([a-zA-Z0-9]+)/',
            function ($agentName) {
                $agent = $this->agentRepository->findOneByNickName($agentName[1]);

                if ($agent) {
                    $url = $this->urlGenerator->generate('agent_show', array('id' => $agent->getId()));

                    return sprintf('<a href="%s">%s</a>', $url, $agentName[0]);
                }

                return '<code>'.$agentName[0].'</code>';
            },
            $text
        );

        return $text;
    }
}
