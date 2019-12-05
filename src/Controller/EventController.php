<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\AgentStatRepository;
use App\Repository\EventRepository;
use App\Service\EventHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/event")
 */
class EventController extends AbstractController
{
    /**
     * @Route("/", name="event_index", methods={"GET"})
     * @IsGranted("ROLE_AGENT")
     */
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render(
            'event/index.html.twig', [
                'events' => $eventRepository->findAll(),
            ]
        );
    }

    /**
     * @Route("/new", name="event_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('event_index');
        }

        return $this->render(
            'event/new.html.twig', [
                'event' => $event,
                'form'  => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="event_show", methods={"GET"})
     * @IsGranted("ROLE_AGENT")
     */
    public function show(Event $event, AgentStatRepository $statRepository, EventHelper $eventHelper): Response
    {

        $entries = $statRepository->findByDate($event->getDateStart(), $event->getDateEnd());

        $values = $eventHelper->calculateResults($event, $entries);

        $now = new \DateTime();

        if ($event->getDateStart() > $now) {
            $status = 'future';
        } elseif ($event->getDateEnd() < $now) {
            $status = 'past';
        } else {
            $status = 'current';
        }

        return $this->render(
            'event/show.html.twig', [
                'event'   => $event,
                'values' => $values,
                'status'  => $status,
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="event_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, Event $event): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('event_index');
        }

        return $this->render(
            'event/edit.html.twig', [
                'event' => $event,
                'form'  => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="event_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Event $event): Response
    {
        if ($this->isCsrfTokenValid(
            'delete'.$event->getId(), $request->request->get('_token')
        )
        ) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('event_index');
    }
}
