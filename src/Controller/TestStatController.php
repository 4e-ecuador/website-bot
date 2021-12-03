<?php

namespace App\Controller;

use App\Entity\TestStat;
use App\Form\TestStatType;
use App\Repository\TestStatRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/test/stat')]
#[IsGranted('ROLE_ADMIN')]
class TestStatController extends BaseController
{
    #[Route(path: '/', name: 'test_stat_index', methods: ['GET'])]
    public function index(TestStatRepository $testStatRepository): Response
    {
        return $this->render(
            'test_stat/index.html.twig',
            [
                'test_stats' => $testStatRepository->findAll(),
            ]
        );
    }

    #[Route(path: '/new', name: 'test_stat_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $testStat = new TestStat();
        $form = $this->createForm(TestStatType::class, $testStat);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($testStat);
            $entityManager->flush();

            return $this->redirectToRoute('test_stat_index');
        }

        return $this->render(
            'test_stat/new.html.twig',
            [
                'test_stat' => $testStat,
                'form'      => $form->createView(),
            ]
        );
    }

    #[Route(path: '/{id}', name: 'test_stat_show', methods: ['GET'])]
    public function show(TestStat $testStat): Response
    {
        return $this->render(
            'test_stat/show.html.twig',
            [
                'test_stat' => $testStat,
            ]
        );
    }

    #[Route(path: '/{id}/edit', name: 'test_stat_edit', methods: [
        'GET',
        'POST',
    ])]
    public function edit(Request $request, TestStat $testStat): Response
    {
        $form = $this->createForm(TestStatType::class, $testStat);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('test_stat_index');
        }

        return $this->render(
            'test_stat/edit.html.twig',
            [
                'test_stat' => $testStat,
                'form'      => $form->createView(),
            ]
        );
    }

    #[Route(path: '/{id}', name: 'test_stat_delete', methods: ['DELETE'])]
    public function delete(Request $request, TestStat $testStat): Response
    {
        if ($this->isCsrfTokenValid(
            'delete'.$testStat->getId(),
            $request->request->get('_token')
        )
        ) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($testStat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('test_stat_index');
    }
}
