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

/**
 * @Route("/help")
 */
class HelpController extends AbstractController
{
    /**
     * @Route("/", name="help_index", methods={"GET"})
     * @IsGranted("ROLE_AGENT")
     */
    public function index(HelpRepository $helpRepository): Response
    {
        return $this->render(
            'help/index.html.twig', [
                'helps' => $helpRepository->findAll(),
            ]
        );
    }

    /**
     * @Route("/new", name="help_new", methods={"GET","POST"})
     * @IsGranted("ROLE_EDITOR")
     */
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
            'help/new.html.twig', [
                'help' => $help,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="help_show", methods={"GET"}, requirements={"id"="\d+"})
     * @IsGranted("ROLE_AGENT")
     */
    public function show(Help $help): Response
    {
        return $this->render(
            'help/show.html.twig', [
                'help' => $help,
            ]
        );
    }

    /**
     * @Route("/page/{slug}", name="help_show2", methods={"GET"})
     * @IsGranted("ROLE_AGENT")
     */
    public function show2(string $slug, HelpRepository $helpRepository): Response
    {
        $help = $helpRepository->findOneBy(['slug' => $slug]);

        if (!$help) {
            throw $this->createNotFoundException();
        }

        return $this->render('help/show.html.twig', ['help' => $help,]);
    }

    /**
     * @Route("/{id}/edit", name="help_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_EDITOR")
     */
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
            'help/edit.html.twig', [
                'help' => $help,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="help_delete", methods={"DELETE"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function delete(Request $request, Help $help): Response
    {
        if ($this->isCsrfTokenValid(
            'delete'.$help->getId(), $request->request->get('_token')
        )
        ) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($help);
            $entityManager->flush();
        }

        return $this->redirectToRoute('help_index');
    }
}
