<?php

namespace App\Controller;

use App\Entity\Challenge;
use App\Form\ChallengeType;
use App\Repository\AgentStatRepository;
use App\Repository\ChallengeRepository;
use App\Service\ChallengeHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/challenge')]
class ChallengeController extends BaseController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route(path: '/', name: 'challenge_index', methods: ['GET'])]
    public function index(ChallengeRepository $challengeRepository): Response
    {
        return $this->render(
            'challenge/index.html.twig',
            [
                'challenges' => $challengeRepository->findAll(),
            ]
        );
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route(path: '/new', name: 'challenge_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $challenge = new Challenge();
        $form = $this->createForm(ChallengeType::class, $challenge);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($challenge);
            $entityManager->flush();

            return $this->redirectToRoute('challenge_index');
        }
        return $this->render(
            'challenge/new.html.twig',
            [
                'challenge' => $challenge,
                'form'      => $form->createView(),
            ]
        );
    }
    /**
     * @IsGranted("ROLE_AGENT")
     */
    #[Route(path: '/{id}', name: 'challenge_show', methods: ['GET'])]
    public function show(Challenge $challenge, AgentStatRepository $statRepository, ChallengeHelper $challengeHelper): Response
    {
        $entries = $statRepository->findByDate(
            $challenge->getDateStart(),
            $challenge->getDateEnd()
        );
        return $this->render(
            'challenge/show.html.twig',
            [
                'challenge' => $challenge,
                'entries'   => $challengeHelper->getResults($entries),
            ]
        );
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route(path: '/{id}/edit', name: 'challenge_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Challenge $challenge): Response
    {
        $form = $this->createForm(ChallengeType::class, $challenge);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('challenge_index');
        }
        return $this->render(
            'challenge/edit.html.twig',
            [
                'challenge' => $challenge,
                'form'      => $form->createView(),
            ]
        );
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route(path: '/{id}', name: 'challenge_delete', methods: ['DELETE'])]
    public function delete(Request $request, Challenge $challenge): Response
    {
        if ($this->isCsrfTokenValid(
            'delete'.$challenge->getId(),
            $request->request->get('_token')
        )
        ) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($challenge);
            $entityManager->flush();
        }
        return $this->redirectToRoute('challenge_index');
    }
}
