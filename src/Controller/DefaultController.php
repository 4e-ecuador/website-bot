<?php

namespace App\Controller;

use App\Repository\AgentRepository;
use App\Repository\ChallengeRepository;
use App\Repository\CommentRepository;
use App\Repository\EventRepository;
use App\Repository\IngressEventRepository;
use App\Service\CalendarHelper;
use App\Service\CiteService;
use App\Service\DateTimeHelper;
use App\Service\EventHelper;
use App\Service\MarkdownHelper;
use DateTime;
use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DefaultController extends AbstractController
{
    #[Route(path: '/', name: 'default', methods: ['GET'], schemes: ['https'])]
    public function index(
        AgentRepository $agentRepository,
        CommentRepository $commentRepository,
        EventRepository $eventRepository,
        IngressEventRepository $ingressEventRepository,
        ChallengeRepository $challengeRepository,
        DateTimeHelper $dateTimeHelper,
        MarkdownHelper $markdownHelper,
        CiteService $citeService,
        CalendarHelper $calendarHelper,
        #[Autowire('%env(DEFAULT_TIMEZONE)%')] string $defaultTimeZone,
    ): Response {
        $calendarHelper->getEvents();
        $comments = [];
        $currentEvents = [];
        $pastEvents = [];
        $futureEvents = [];
        $ingressFS = [];
        $ingressMD = [];
        $challenges = [];
        $tz = new DateTimeZone($defaultTimeZone);
        $now = new DateTime('now', $tz);
        $now2 = new DateTime();
        if ($this->isGranted('ROLE_AGENT')) {
            $comments = $commentRepository->findLatest(5);

            foreach ($comments as $comment) {
                $comment->setText($markdownHelper->parse($comment->getText()));
            }

            $events = $eventRepository->findAll();

            foreach ($events as $event) {
                $event->setDateStart(
                    new DateTime(
                        $event->getDateStart()->format('Y-m-d H:i:s'), $tz
                    )
                );
                $event->setDateEnd(
                    new DateTime(
                        $event->getDateEnd()->format('Y-m-d H:i:s'), $tz
                    )
                );
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
            $challenges = $challengeRepository->findCurrent();
        }

        return $this->render(
            'default/index.html.twig',
            [
                'now'            => $now,
                'now2'           => $now2,
                'agents'         => $agentRepository->findAll(),
                'latestComments' => $comments,
                'pastEvents'     => $pastEvents,
                'currentEvents'  => $currentEvents,
                'futureEvents'   => $futureEvents,
                'ingressFS'      => $ingressFS,
                'ingressMD'      => $ingressMD,
                'nextFs'         => $dateTimeHelper->getNextFS(),
                'challenges'     => $challenges,
                'cite'           => $citeService->getRandomCite(),
            ]
        );
    }

    #[Route(path: '/calendar', name: 'event_calendar', methods: ['GET'])]
    public function calendar(): Response
    {
        return $this->render('event/calendar.html.twig');
    }

    #[Route(path: '/events', name: 'default_events', methods: ['GET'])]
    #[IsGranted('ROLE_AGENT')]
    public function events(
        EventHelper $eventHelper,
        IngressEventRepository $ingressEventRepository
    ): Response {
        return $this->render(
            'default/events.html.twig',
            [
                'ingressFS'         => $ingressEventRepository->findFutureFS(),
                'ingressMD'         => $ingressEventRepository->findFutureMD(),
                'pastEvents'        => $eventHelper->getEventsInSpan('past'),
                'currentEvents'     => $eventHelper->getEventsInSpan('present'),
                'futureEvents'      => $eventHelper->getEventsInSpan('future'),
                'pastChallenges'    => $eventHelper->getChallengesInSpan(
                    'past'
                ),
                'currentChallenges' => $eventHelper->getChallengesInSpan(
                    'present'
                ),
                'futureChallenges'  => $eventHelper->getChallengesInSpan(
                    'future'
                ),
            ]
        );
    }

    #[Route(path: '/privacy', name: 'app_privacy', methods: ['GET'])]
    public function privacy(): Response
    {
        return $this->render('default/privacy.html.twig');
    }
}
