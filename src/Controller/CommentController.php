<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\AgentRepository;
use App\Repository\CommentRepository;
use App\Service\MarkdownHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/comment')]
class CommentController extends BaseController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route(path: '/', name: 'comment_index', methods: ['GET'])]
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
     * @IsGranted("ROLE_EDITOR")
     */
    #[Route(path: '/new', name: 'comment_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
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
     * @IsGranted("ROLE_EDITOR")
     */
    #[Route(path: '/fetch', name: 'comment_fetch', methods: ['GET', 'POST'])]
    public function getSingle(
        Request $request,
        CommentRepository $commentRepository,
        MarkdownHelper $markdownHelper
    ): JsonResponse {
        $commentId = $request->request->get('comment_id');
        $comment = $commentRepository->findOneBy(['id' => $commentId]);
        if (!$comment) {
            throw $this->createNotFoundException();
        }
        $comment->setText($markdownHelper->parse($comment->getText()));
        $html = $this->renderView(
            'comment/commentBox.html.twig',
            [
                'comment' => $comment,
            ]
        );

        return $this->json(['comment' => $html]);
    }

    /**
     * @IsGranted("ROLE_EDITOR")
     */
    #[Route(path: '/{id}', name: 'comment_show', requirements: ['id' => '\d+'], methods: ['GET'])]
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
     * @IsGranted("ROLE_EDITOR")
     */
    #[Route(path: '/{id}/edit', name: 'comment_edit', methods: ['GET', 'POST'])]
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
     * @IsGranted("ROLE_EDITOR")
     */
    #[Route(path: '/{id}', name: 'comment_delete', methods: ['DELETE'])]
    public function delete(Request $request, Comment $comment): Response
    {
        if ($this->isCsrfTokenValid(
            'delete'.$comment->getId(),
            $request->request->get('_token')
        )
        ) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('comment_index');
    }

    /**
     * @IsGranted("ROLE_EDITOR")
     */
    #[Route(path: '/deleteinline/{id}', name: 'comment_delete_inline', methods: ['DELETE'])]
    public function deleteInline(
        Request $request,
        Comment $comment
    ): JsonResponse {
        $response = ['status' => 'ok'];

        return $this->json($response);
        if ($this->isCsrfTokenValid(
            'delete'.$comment->getId(),
            $request->request->get('_token')
        )
        ) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('comment_index');
    }

    /**
     * @IsGranted("ROLE_EDITOR")
     */
    #[Route(path: '/getagentids', name: 'comment_agent_ids')]
    public function getAgentCommentIds(
        Request $request,
        AgentRepository $agentRepository,
        MarkdownHelper $markdownHelper
    ): JsonResponse {
        $html = '';
        $agentId = $request->request->get('agent_id');
        $agent = $agentRepository->findOneBy(['id' => $agentId]);
        foreach ($agent->getComments() as $comment) {
            $comment->setText($markdownHelper->parse($comment->getText()));

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
