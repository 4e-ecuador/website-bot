<?php

namespace App\Controller;

use App\Repository\AgentRepository;
use App\Repository\CommentRepository;
use App\Repository\EventRepository;
use App\Repository\IngressEventRepository;
use App\Service\EventHelper;
use App\Service\MarkdownHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index(AgentRepository $agentRepository, CommentRepository $commentRepository, EventRepository $eventRepository, IngressEventRepository $ingressEventRepository, EventHelper $eventHelper, MarkdownHelper $markdownHelper): Response
    {
        $comments = [];
        $currentEvents = [];
        $pastEvents = [];
        $futureEvents = [];
        $ingressFS = [];
        $ingressMD = [];
        $now = new \DateTime('now', new \DateTimeZone($_ENV['DEFAULT_TIMEZONE']));
        $now2 = new \DateTime();

        if ($this->isGranted('ROLE_AGENT')) {
            $comments = $commentRepository->findLatest(5);

            foreach ($comments as $comment) {
                $comment->setText($markdownHelper->parse($comment->getText()));
            }

            $events = $eventRepository->findAll();

            $now = new \DateTime('now', new \DateTimeZone($_ENV['DEFAULT_TIMEZONE']));

            foreach ($events as $event) {
                if ($event->getDateStart() > $now) {
                    $futureEvents[] = $event;
                } elseif ($event->getDateEnd() < $now) {
                    $pastEvents[] = $event;
                } else {
                    $currentEvents[] = $event;
                }
            }

            $ingressFS = $ingressEventRepository->findFutureFS();
            $ingressMD = $ingressEventRepository->findFutureMD();
        }

        return $this->render(
            'default/index.html.twig',
            [
                'now' => $now,
                'now2' => $now2,
                'agents'         => $agentRepository->findAll(),
                'latestComments' => $comments,
                'pastEvents'     => $pastEvents,
                'currentEvents'  => $currentEvents,
                'futureEvents'   => $futureEvents,
                'ingressFS'      => $ingressFS,
                'ingressMD'      => $ingressMD,
                'nextFs'         => $eventHelper->getNextFS(),
            ]
        );
    }
}
