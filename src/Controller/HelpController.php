<?php

namespace App\Controller;

use App\Entity\Help;
use App\Form\HelpType;
use App\Repository\HelpRepository;
use App\Util\Slugger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/help')]
class HelpController extends BaseController
{
    /**
     * @IsGranted("ROLE_AGENT")
     */
    #[Route(path: '/', name: 'help_index', methods: ['GET'])]
    public function index(HelpRepository $helpRepository): Response
    {
        return $this->render(
            'help/index.html.twig',
            [
                'helps' => $helpRepository->findAll(),
            ]
        );
    }
    /**
     * @IsGranted("ROLE_EDITOR")
     */
    #[Route(path: '/new', name: 'help_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $help = new Help();
        $form = $this->createForm(HelpType::class, $help);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $help->setSlug(Slugger::slugify($help->getTitle()));
            $entityManager = $this->getDoctrine()->getManager();
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
    /**
     * @IsGranted("ROLE_AGENT")
     */
    #[Route(path: '/{id}', name: 'help_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Help $help): Response
    {
        return $this->render(
            'help/show.html.twig',
            [
                'help' => $help,
            ]
        );
    }
    /**
     * @IsGranted("ROLE_AGENT")
     */
    #[Route(path: '/page/{slug}', name: 'help_show2', methods: ['GET'])]
    public function show2(string $slug, HelpRepository $helpRepository): Response
    {
        $help = $helpRepository->findOneBy(['slug' => $slug]);
        if (!$help) {
            throw $this->createNotFoundException();
        }
        return $this->render('help/show.html.twig', ['help' => $help,]);
    }
    /**
     * @IsGranted("ROLE_EDITOR")
     */
    #[Route(path: '/{id}/edit', name: 'help_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Help $help): Response
    {
        $form = $this->createForm(HelpType::class, $help);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $help->setSlug(Slugger::slugify($help->getTitle()));

            $this->getDoctrine()->getManager()->flush();

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
    /**
     * @IsGranted("ROLE_EDITOR")
     */
    #[Route(path: '/{id}', name: 'help_delete', methods: ['DELETE'])]
    public function delete(Request $request, Help $help): Response
    {
        if ($this->isCsrfTokenValid(
            'delete'.$help->getId(),
            $request->request->get('_token')
        )
        ) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($help);
            $entityManager->flush();
        }
        return $this->redirectToRoute('help_index');
    }
}
