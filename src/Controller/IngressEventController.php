<?php

namespace App\Controller;

use App\Entity\IngressEvent;
use App\Form\IngressEventType;
use App\Repository\IngressEventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ingress/event")
 */
class IngressEventController extends AbstractController
{
    /**
     * @Route("/", name="ingress_event_index", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(IngressEventRepository $ingressEventRepository): Response
    {
        return $this->render(
            'ingress_event/index.html.twig', [
                'ingress_events' => $ingressEventRepository->findAll(),
            ]
        );
    }

    /**
     * @Route("/new", name="ingress_event_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request): Response
    {
        $ingressEvent = new IngressEvent();
        $form = $this->createForm(IngressEventType::class, $ingressEvent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($ingressEvent->getDateEnd() < $ingressEvent->getDateStart()) {
                $ingressEvent->setDateEnd($ingressEvent->getDateStart());
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ingressEvent);
            $entityManager->flush();

            return $this->redirectToRoute('ingress_event_index');
        }

        return $this->render(
            'ingress_event/new.html.twig', [
                'ingress_event' => $ingressEvent,
                'form'          => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="ingress_event_show", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function show(IngressEvent $ingressEvent): Response
    {
        return $this->render(
            'ingress_event/show.html.twig', [
                'ingress_event' => $ingressEvent,
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="ingress_event_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, IngressEvent $ingressEvent): Response
    {
        $form = $this->createForm(IngressEventType::class, $ingressEvent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('ingress_event_index');
        }

        return $this->render(
            'ingress_event/edit.html.twig', [
                'ingress_event' => $ingressEvent,
                'form'          => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="ingress_event_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, IngressEvent $ingressEvent): Response
    {
        if ($this->isCsrfTokenValid(
            'delete'.$ingressEvent->getId(), $request->request->get('_token')
        )
        ) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($ingressEvent);
            $entityManager->flush();
        }

        return $this->redirectToRoute('ingress_event_index');
    }
}
