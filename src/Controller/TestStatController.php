<?php

namespace App\Controller;

use App\Entity\TestStat;
use App\Form\TestStatType;
use App\Repository\TestStatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class TestStatController extends BaseController
{
    public function __construct(
        private readonly TestStatRepository $testStatRepository
    ) {
    }

    #[Route(path: '/test/stat/', name: 'test_stat_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render(
            'test_stat/index.html.twig',
            [
                'test_stats' => $this->testStatRepository->findAll(),
            ]
        );
    }

    #[Route(path: '/test/stat/new', name: 'test_stat_new', methods: [
        'GET',
        'POST',
    ])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $testStat = new TestStat();
        $form = $this->createForm(TestStatType::class, $testStat);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($testStat);
            $entityManager->flush();

            return $this->redirectToRoute('test_stat_index');
        }

        return $this->render(
            'test_stat/new.html.twig',
            [
                'test_stat' => $testStat,
                'form'      => $form,
            ]
        );
    }

    #[Route(path: '/test/stat/{id}', name: 'test_stat_show', methods: ['GET'])]
    public function show(TestStat $testStat): Response
    {
        return $this->render(
            'test_stat/show.html.twig',
            [
                'test_stat' => $testStat,
            ]
        );
    }

    #[Route(path: '/test/stat/{id}/edit', name: 'test_stat_edit', methods: [
        'GET',
        'POST',
    ])]
    public function edit(
        Request $request,
        TestStat $testStat,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(TestStatType::class, $testStat);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('test_stat_index');
        }

        return $this->render(
            'test_stat/edit.html.twig',
            [
                'test_stat' => $testStat,
                'form'      => $form,
            ]
        );
    }

    #[Route(path: '/test/stat/{id}', name: 'test_stat_delete', methods: ['DELETE'])]
    public function delete(
        Request $request,
        TestStat $testStat,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete'.$testStat->getId(),
            (string)$request->request->get('_token')
        )
        ) {
            $entityManager->remove($testStat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('test_stat_index');
    }
}
