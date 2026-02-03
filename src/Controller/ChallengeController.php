<?php

namespace App\Controller;

use App\Entity\Challenge;
use App\Form\ChallengeType;
use App\Repository\AgentStatRepository;
use App\Repository\ChallengeRepository;
use App\Service\ChallengeHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ChallengeController extends BaseController
{
    public function __construct(
        private readonly ChallengeRepository $challengeRepository,
        private readonly AgentStatRepository $statRepository,
        private readonly ChallengeHelper $challengeHelper
    ) {
    }

    #[Route(path: '/challenge/', name: 'challenge_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        return $this->render(
            'challenge/index.html.twig',
            [
                'challenges' => $this->challengeRepository->findAll(),
            ]
        );
    }

    #[Route(path: '/challenge/new', name: 'challenge_new', methods: [
        'GET',
        'POST',
    ])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $challenge = new Challenge();
        $form = $this->createForm(ChallengeType::class, $challenge);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($challenge);
            $entityManager->flush();

            return $this->redirectToRoute('challenge_index');
        }

        return $this->render(
            'challenge/new.html.twig',
            [
                'challenge' => $challenge,
                'form'      => $form,
            ]
        );
    }

    #[Route(path: '/challenge/{id}', name: 'challenge_show', methods: ['GET'])]
    #[IsGranted('ROLE_AGENT')]
    public function show(
        Challenge $challenge
    ): Response {
        $entries = (array)$this->statRepository->findByDate(
            $challenge->getDateStart(),
            $challenge->getDateEnd()
        );

        return $this->render(
            'challenge/show.html.twig',
            [
                'challenge' => $challenge,
                'entries'   => $this->challengeHelper->getResults($entries),
            ]
        );
    }

    #[Route(path: '/challenge/{id}/edit', name: 'challenge_edit', methods: [
        'GET',
        'POST',
    ])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        Request $request,
        Challenge $challenge,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(ChallengeType::class, $challenge);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('challenge_index');
        }

        return $this->render(
            'challenge/edit.html.twig',
            [
                'challenge' => $challenge,
                'form'      => $form,
            ]
        );
    }

    #[Route(path: '/challenge/{id}', name: 'challenge_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        Request $request,
        Challenge $challenge,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete'.$challenge->getId(),
            (string)$request->request->get('_token')
        )
        ) {
            $entityManager->remove($challenge);
            $entityManager->flush();
        }

        return $this->redirectToRoute('challenge_index');
    }
}
