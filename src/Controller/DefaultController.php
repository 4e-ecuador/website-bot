<?php

namespace App\Controller;

use App\Repository\AgentRepository;
use App\Repository\CommentRepository;
use App\Repository\EventRepository;
use App\Service\MarkdownHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index(AgentRepository $agentRepository, CommentRepository $commentRepository, EventRepository $eventRepository, MarkdownHelper $markdownHelper): Response
    {
        $comments = [];
        $currentEvents = [];
        $pastEvents = [];
        $futureEvents = [];

        if ($this->isGranted('ROLE_AGENT')) {
            $comments = $commentRepository->findLatest(5);

            foreach ($comments as $comment) {
                $comment->setText($markdownHelper->parse($comment->getText()));
            }

            $events = $eventRepository->findAll();

            $now = new \DateTime();

            foreach ($events as $event) {
                if ($event->getDateStart() > $now) {
                    $futureEvents[] = $event;
                } elseif ($event->getDateEnd() < $now) {
                    $pastEvents[] = $event;
                } else {
                    $currentEvents[] = $event;
                }
            }
        }

        return $this->render(
            'default/index.html.twig',
            [
                'agents'         => $agentRepository->findAll(),
                'latestComments' => $comments,
                'pastEvents'     => $pastEvents,
                'currentEvents'  => $currentEvents,
                'futureEvents'   => $futureEvents,
            ]
        );
    }
}
