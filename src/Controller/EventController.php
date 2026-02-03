<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\AgentStatRepository;
use App\Repository\EventRepository;
use App\Service\EventHelper;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EventController extends BaseController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly AgentStatRepository $statRepository,
        private readonly EventHelper $eventHelper
    ) {
    }

    #[Route(path: '/event/', name: 'event_index', methods: ['GET'])]
    #[IsGranted('ROLE_AGENT')]
    public function index(): Response
    {
        return $this->render(
            'event/index.html.twig',
            [
                'events' => $this->eventRepository->findAll(),
            ]
        );
    }

    #[Route(path: '/event/new', name: 'event_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('event_index');
        }

        return $this->render(
            'event/new.html.twig',
            [
                'event' => $event,
                'form'  => $form,
            ]
        );
    }

    #[Route(path: '/event/{id}', name: 'event_show', methods: ['GET'])]
    #[IsGranted('ROLE_AGENT')]
    public function show(Event $event): Response
    {
        $entries = $this->statRepository->findByDate(
            $event->getDateStart(),
            $event->getDateEnd()
        );
        $values = $this->eventHelper->calculateResults($event, $entries);
        $now = new DateTime();
        if ($event->getDateStart() > $now) {
            $status = 'future';
        } elseif ($event->getDateEnd() < $now) {
            $status = 'past';
        } else {
            $status = 'current';
        }

        return $this->render(
            'event/show.html.twig',
            [
                'event' => $event,
                'values' => $values,
                'status' => $status,
            ]
        );
    }

    #[Route(path: '/event/{id}/edit', name: 'event_edit', methods: [
        'GET',
        'POST',
    ])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        Request $request,
        Event $event,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('event_index');
        }

        return $this->render(
            'event/edit.html.twig',
            [
                'event' => $event,
                'form'  => $form,
            ]
        );
    }

    #[Route(path: '/event/{id}', name: 'event_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        Request $request,
        Event $event,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete'.$event->getId(),
            (string)$request->request->get('_token')
        )
        ) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('event_index');
    }
}
