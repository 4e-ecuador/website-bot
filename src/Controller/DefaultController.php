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
    public function index(AgentRepository $agentRepository)
    {
        return $this->render('default/index.html.twig', [
            'agents' => $agentRepository->findAll(),
        ]);
    }
}
