<?php

namespace App\Controller;

use App\Entity\Help;
use App\Form\HelpType;
use App\Repository\HelpRepository;
use App\Util\Slugger;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/help')]
class HelpController extends BaseController
{
    #[Route(path: '/', name: 'help_index', methods: ['GET'])]
    #[IsGranted('ROLE_AGENT')]
    public function index(HelpRepository $helpRepository): Response
    {
        return $this->render(
            'help/index.html.twig',
            [
                'helps' => $helpRepository->findAll(),
            ]
        );
    }

    #[Route(path: '/new', name: 'help_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_EDITOR')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $help = new Help();
        $form = $this->createForm(HelpType::class, $help);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $help->setSlug(Slugger::slugify($help->getTitle()));
            $entityManager->persist($help);
            $entityManager->flush();

            return $this->redirectToRoute('help_index');
        }

        return $this->render(
            'help/new.html.twig',
            [
                'help' => $help,
                'form' => $form->createView(),
            ]
        );
    }

    #[Route(path: '/{id}', name: 'help_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_AGENT')]
    public function show(Help $help): Response
    {
        return $this->render(
            'help/show.html.twig',
            [
                'help' => $help,
            ]
        );
    }

    #[Route(path: '/page/{slug}', name: 'help_show2', methods: ['GET'])]
    #[IsGranted('ROLE_AGENT')]
    public function show2(
        string $slug,
        HelpRepository $helpRepository
    ): Response {
        $help = $helpRepository->findOneBy(['slug' => $slug]);
        if (!$help) {
            throw $this->createNotFoundException();
        }

        return $this->render('help/show.html.twig', ['help' => $help,]);
    }

    #[Route(path: '/{id}/edit', name: 'help_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_EDITOR')]
    public function edit(
        Request $request,
        Help $help,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(HelpType::class, $help);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $help->setSlug(Slugger::slugify($help->getTitle()));

            $entityManager->flush();

            return $this->redirectToRoute('help_index');
        }

        return $this->render(
            'help/edit.html.twig',
            [
                'help' => $help,
                'form' => $form->createView(),
            ]
        );
    }

    #[Route(path: '/{id}', name: 'help_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_EDITOR')]
    public function delete(
        Request $request,
        Help $help,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete'.$help->getId(),
            (string)$request->request->get('_token')
        )
        ) {
            $entityManager->remove($help);
            $entityManager->flush();
        }

        return $this->redirectToRoute('help_index');
    }
}
