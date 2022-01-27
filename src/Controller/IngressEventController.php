<?php

namespace App\Controller;

use App\Entity\IngressEvent;
use App\Form\IngressEventType;
use App\Helper\Paginator\PaginatorTrait;
use App\Repository\AgentRepository;
use App\Repository\IngressEventRepository;
use App\Repository\UserRepository;
use App\Service\FcmHelper;
use App\Service\TelegramBotHelper;
use App\Type\CustomMessage\NotifyEventsMessage;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Goutte\Client;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use stdClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function count;

#[Route(path: '/ingress/event')]
class IngressEventController extends BaseController
{
    use PaginatorTrait;

    #[Route(path: '/', name: 'ingress_event_index', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(
        IngressEventRepository $ingressEventRepository,
        Request $request
    ): Response {
        $paginatorOptions = $this->getPaginatorOptions($request);
        $events = $ingressEventRepository->getPaginatedList($paginatorOptions);
        $paginatorOptions->setMaxPages(
            ceil(count($events) / $paginatorOptions->getLimit())
        );

        return $this->render(
            'ingress_event/index.html.twig',
            [
                'ingress_events'   => $events,
                'paginatorOptions' => $paginatorOptions,
            ]
        );
    }

    #[Route(path: '/new', name: 'ingress_event_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ingressEvent = new IngressEvent();
        $form = $this->createForm(IngressEventType::class, $ingressEvent);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($ingressEvent->getDateEnd() < $ingressEvent->getDateStart()) {
                $ingressEvent->setDateEnd($ingressEvent->getDateStart());
            }
            $entityManager->persist($ingressEvent);
            $entityManager->flush();

            return $this->redirectToRoute('ingress_event_index');
        }

        return $this->render(
            'ingress_event/new.html.twig',
            [
                'ingress_event' => $ingressEvent,
                'form'          => $form->createView(),
            ]
        );
    }

    #[Route(path: '/show/{id}', name: 'ingress_event_public_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function publicShow(IngressEvent $ingressEvent): Response
    {
        return $this->render(
            'ingress_event/public_show.html.twig',
            [
                'ingress_event' => $ingressEvent,
            ]
        );
    }

    #[Route(path: '/{id}', name: 'ingress_event_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(IngressEvent $ingressEvent): Response
    {
        return $this->render(
            'ingress_event/show.html.twig',
            [
                'ingress_event' => $ingressEvent,
            ]
        );
    }

    #[Route(path: '/{id}/edit', name: 'ingress_event_edit', methods: [
        'GET',
        'POST',
    ])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, IngressEvent $ingressEvent, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(IngressEventType::class, $ingressEvent);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('ingress_event_index');
        }

        return $this->render(
            'ingress_event/edit.html.twig',
            [
                'ingress_event' => $ingressEvent,
                'form'          => $form->createView(),
            ]
        );
    }

    #[Route(path: '/{id}', name: 'ingress_event_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        Request $request,
        IngressEvent $ingressEvent,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete'.$ingressEvent->getId(),
            $request->request->get('_token')
        )
        ) {
            $entityManager->remove($ingressEvent);
            $entityManager->flush();
        }

        return $this->redirectToRoute('ingress_event_index');
    }

    #[Route(path: '/announce', name: 'ingress_event_announce', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function announceTg(
        TelegramBotHelper $telegramBotHelper,
        AgentRepository $agentRepository,
        NotifyEventsMessage $notifyEventsMessage
    ): RedirectResponse {
        $message = $notifyEventsMessage
            ->setFirstAnnounce(true)
            ->getText();
        if (!$message) {
            $this->addFlash('warning', 'No events to announce ;(');

            return $this->redirectToRoute('ingress_event_index');
        }
        $agents = $agentRepository->findNotifyAgents();
        $count = 0;
        foreach ($agents as $agent) {
            if ($agent->getHasNotifyEvents()) {
                try {
                    $telegramBotHelper->sendMessage(
                        $agent->getTelegramId(),
                        $message,
                        true
                    );

                    $count++;
                } catch (Exception $exception) {
                    $this->addFlash(
                        'warning',
                        $exception->getMessage().' - Agent: '
                        .$agent->getNickname()
                    );
                }
            }
        }
        if ($count) {
            $this->addFlash(
                'success',
                sprintf('Announcements sent to %d agents!', $count)
            );
        }

        return $this->redirectToRoute('ingress_event_index');
    }

    #[Route(path: '/announce-fbm', name: 'ingress_event_announce_fbm', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function announceFbm(
        FcmHelper $fbmHelper,
        NotifyEventsMessage $notifyEventsMessage
    ): RedirectResponse {
        try {
            $message = $notifyEventsMessage
                ->setFirstAnnounce(true)
                ->getText();

            if (!$message) {
                throw new RuntimeException('No events to announce ;(');
            }

            $title = 'Nuevos Eventos Ingress!';

            $fbmHelper->sendMessage($title, implode("\n", $message));
            $this->addFlash('success', 'Announcement has been sent.');
        } catch (Exception $exception) {
            $this->addFlash('danger', 'Error: '.$exception->getMessage());
        }

        return $this->redirectToRoute('ingress_event_index');
    }

    #[Route(path: '/announce-fbm-token', name: 'ingress_event_announce_fbm_token', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function announceFbmToken(
        FcmHelper $fbmHelper,
        UserRepository $userRepository,
        NotifyEventsMessage $notifyEventsMessage
    ): RedirectResponse {
        try {
            $message = $notifyEventsMessage
                ->setFirstAnnounce(true)
                ->getText();

            if (!$message) {
                throw new RuntimeException('No events to announce ;(');
            }

            $users = $userRepository->getFireBaseUsers();
            $count = 0;
            $title = 'Nuevos Eventos Ingress!';
            $tokens = [];

            foreach ($users as $user) {
                $tokens[] = $user->getFireBaseToken();
                $count++;
            }

            if (!$fbmHelper->sendMessageWithTokens(
                'URG '.$title,
                $message,
                $tokens
            )
            ) {
                $this->addFlash(
                    'warning',
                    'Message not sent :'
                );
            }

            if ($count) {
                $this->addFlash(
                    'success',
                    sprintf('Announcements sent to %d agents!', $count)
                );
            } else {
                $this->addFlash('warning', 'No messages sent :(');
            }
        } catch (Exception $exception) {
            $this->addFlash('danger', 'Error: '.$exception->getMessage());
        }

        return $this->redirectToRoute('ingress_event_index');
    }

    #[Route(path: '/overview', name: 'ingress_event_overview', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function overview(IngressEventRepository $ingressEventRepository
    ): Response {
        $events = $ingressEventRepository->findFutureFS();
        $eventIds = [];
        foreach ($events as $event) {
            $eventIds[] = $event->getId();
        }

        return $this->render(
            'ingress_event/overview.html.twig',
            [
                'events'   => $events,
                'eventIds' => $eventIds,
            ]
        );
    }

    #[Route(path: '/fetch-overview/{id}', name: 'ingress_event_overview_fetch', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function fetchOverview(IngressEvent $event): JsonResponse
    {
        if (!$event->getLink()) {
            return $this->json(['error' => 'no link provided']);
        }
        $client = new Client();
        $info = new stdClass();
        $info->poc = [];
        $info->atendees = [];
        $crawler = $client->request('GET', $event->getLink());
        $crawler->filterXPath('//table/tbody/tr/td/a')->each(
            static function ($node) use ($info) {
                $info->poc[$node->attr('class')] = $node->html();
            }
        );
        $crawler->filterXPath('//table/tbody/tr/td/div')->each(
            static function ($node) use ($info) {
                static $i = 0;
                $string = $node->html();
                $string = preg_replace(
                    '#<h4>[\s()\w]+</h4>#m',
                    '',
                    $string
                );

                $atendees = explode('<br>', trim($string));
                $factions = array_keys($info->poc);

                $info->atendees[$factions[$i]] = $atendees;
                $i++;
            }
        );

        return $this->json($info);
    }
}
