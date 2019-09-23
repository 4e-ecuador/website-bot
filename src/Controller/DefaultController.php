<?php

namespace App\Controller;

use App\Repository\AgentRepository;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index(
        AgentRepository $agentRepository,
        CommentRepository $commentRepository
    ): Response {
        return $this->render(
            'default/index.html.twig',
            [
                'agents'         => $agentRepository->findAll(),
                'latestComments' => $commentRepository->findLatestTen(),
            ]
        );
    }
}
