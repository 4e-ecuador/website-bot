<?php

namespace App\Controller;

use App\Repository\AgentRepository;
use App\Service\Templater;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index(AgentRepository $agentRepository, Templater $templater)
    {
        $agent = $agentRepository->findOneBy(['id' => 1]);

        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
            'agents' => $agentRepository->findAll(),
            'foo' => $templater->replaceAgentTemplate('agent-info.md', $agent),

        ]);
    }
}
