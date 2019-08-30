<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\AgentRepository;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/comment")
 */
class CommentController extends AbstractController
{
    /**
     * @Route("/", name="comment_index", methods={"GET"})
     */
    public function index(CommentRepository $commentRepository): Response
    {
        return $this->render(
            'comment/index.html.twig',
            [
                'comments' => $commentRepository->findAll(),
            ]
        );
    }

    /**
     * @Route("/new", name="comment_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $comment = new Comment();
        $form    = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('comment_index');
        }

        return $this->render(
            'comment/new.html.twig',
            [
                'comment' => $comment,
                'form'    => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/fetch", name="comment_fetch", methods={"GET","POST"})
     */
    public function getSingle(Request $request, CommentRepository $commentRepository)
    {
        $commentId = $request->request->get('comment_id');

        $comment = $commentRepository->findOneBy(['id' => $commentId]);

        $html = $this->renderView(
            'comment/commentBox.html.twig',
            [
                'comment' => $comment,
            ]
        );

        return $this->json(['comment' => $html]);
    }

    /**
     * @Route("/{id}", name="comment_show", requirements={"id"="\d+"}, methods={"GET"})
     */
    public function show(Comment $comment): Response
    {
        return $this->render(
            'comment/show.html.twig',
            [
                'comment' => $comment,
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="comment_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Comment $comment): Response
    {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('comment_index');
        }

        return $this->render(
            'comment/edit.html.twig',
            [
                'comment' => $comment,
                'form'    => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="comment_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Comment $comment): Response
    {
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('comment_index');
    }
    /**
     * @Route("/deleteinline/{id}", name="comment_delete_inline", methods={"DELETE"})
     */
    public function deleteInline(Request $request, Comment $comment): JsonResponse
    {
        $response = ['status' => 'ok'];

        return $this->json($response);

        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('comment_index');
    }

    /**
     * @Route("/getagentids", name="comment_agent_ids")
     */
    public function getAgentCommentIds(
        Request $request,
        CommentRepository $commentRepository,
        AgentRepository $agentRepository
    ) {
        $html = '';
        $agentId = $request->request->get('agent_id');
        $agent = $agentRepository->findOneBy(['id' => $agentId]);
        foreach ($agent->getComments() as $comment) {
            $html .= $this->renderView(
                'comment/commentBox.html.twig',
                [
                    'comment' => $comment,
                ]
            );
        }

        return $this->json(['comments' => $html]);
    }
}
