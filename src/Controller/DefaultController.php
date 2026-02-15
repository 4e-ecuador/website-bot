<?php

namespace App\Controller;

use App\Repository\AgentRepository;
use App\Repository\ChallengeRepository;
use App\Repository\CommentRepository;
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
    public function __construct(
        private readonly AgentRepository $agentRepository,
        private readonly CommentRepository $commentRepository,
        private readonly IngressEventRepository $ingressEventRepository,
        private readonly ChallengeRepository $challengeRepository,
        private readonly DateTimeHelper $dateTimeHelper,
        private readonly MarkdownHelper $markdownHelper,
        private readonly CiteService $citeService,
        private readonly CalendarHelper $calendarHelper,
        private readonly EventHelper $eventHelper
    ) {
    }

    #[Route(path: '/', name: 'default', methods: ['GET'], schemes: ['https'])]
    public function index(
        #[Autowire('%env(DEFAULT_TIMEZONE)%')] string $defaultTimeZone,
    ): Response {
        $this->calendarHelper->getEvents();
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
            $comments = $this->commentRepository->findLatest(5);

            foreach ($comments as $comment) {
                $comment->setText(
                    $this->markdownHelper->parse($comment->getText())
                );
            }

            $pastEvents = $this->eventHelper->getEventsInSpan('past');
            $currentEvents = $this->eventHelper->getEventsInSpan('present');
            $futureEvents = $this->eventHelper->getEventsInSpan('future');

            $ingressFS = $this->ingressEventRepository->findFutureFS();
            $ingressMD = $this->ingressEventRepository->findFutureMD();
            $challenges = $this->challengeRepository->findCurrent();
        }

        return $this->render(
            'default/index.html.twig',
            [
                'now'            => $now,
                'now2'           => $now2,
                'agents'         => $this->agentRepository->findAll(),
                'latestComments' => $comments,
                'pastEvents'     => $pastEvents,
                'currentEvents'  => $currentEvents,
                'futureEvents'   => $futureEvents,
                'ingressFS'      => $ingressFS,
                'ingressMD'      => $ingressMD,
                'nextFs'         => $this->dateTimeHelper->getNextFS(),
                'challenges'     => $challenges,
                'cite'           => $this->citeService->getRandomCite(),
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
    public function events(): Response
    {
        return $this->render(
            'default/events.html.twig',
            [
                'ingressFS'         =>
                    $this->ingressEventRepository->findFutureFS(),
                'ingressMD'         =>
                    $this->ingressEventRepository->findFutureMD(),
                'pastEvents'        =>
                    $this->eventHelper->getEventsInSpan('past'),
                'currentEvents'     =>
                    $this->eventHelper->getEventsInSpan('present'),
                'futureEvents'      =>
                    $this->eventHelper->getEventsInSpan('future'),
                'pastChallenges'    =>
                    $this->eventHelper->getChallengesInSpan('past'),
                'currentChallenges' =>
                    $this->eventHelper->getChallengesInSpan('present'),
                'futureChallenges'  =>
                    $this->eventHelper->getChallengesInSpan('future'),
            ]
        );
    }

    #[Route(path: '/privacy', name: 'app_privacy', methods: ['GET'])]
    public function privacy(): Response
    {
        return $this->render('default/privacy.html.twig');
    }
}
