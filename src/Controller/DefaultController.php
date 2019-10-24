<?php

namespace App\Controller;

use App\Repository\AgentRepository;
use App\Repository\CommentRepository;
use App\Service\MarkdownHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index(AgentRepository $agentRepository, CommentRepository $commentRepository, MarkdownHelper $markdownHelper): Response
    {
        $comments = $commentRepository->findLatestTen();

        foreach ($comments as $comment) {
            $comment->setText($markdownHelper->parse($comment->getText()));
        }

        return $this->render(
            'default/index.html.twig',
            [
                'agents'         => $agentRepository->findAll(),
                'latestComments' => $comments,
            ]
        );
    }
}
