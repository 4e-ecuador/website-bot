<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\AgentRepository;
use App\Repository\CommentRepository;
use App\Service\MarkdownHelper;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/comment')]
class CommentController extends BaseController
{
    #[Route(path: '/', name: 'comment_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(CommentRepository $commentRepository): Response
    {
        return $this->render(
            'comment/index.html.twig',
            [
                'comments' => $commentRepository->findAll(),
            ]
        );
    }

    #[Route(path: '/new', name: 'comment_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_EDITOR')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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

    #[Route(path: '/fetch', name: 'comment_fetch', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_EDITOR')]
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

    #[Route(path: '/{id}', name: 'comment_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_EDITOR')]
    public function show(Comment $comment): Response
    {
        return $this->render(
            'comment/show.html.twig',
            [
                'comment' => $comment,
            ]
        );
    }

    #[Route(path: '/{id}/edit', name: 'comment_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_EDITOR')]
    public function edit(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

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

    #[Route(path: '/{id}', name: 'comment_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_EDITOR')]
    public function delete(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid(
            'delete'.$comment->getId(),
            $request->request->get('_token')
        )
        ) {
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('comment_index');
    }

    #[Route(path: '/deleteinline/{id}', name: 'comment_delete_inline', methods: ['DELETE'])]
    #[IsGranted('ROLE_EDITOR')]
    public function deleteInline(
        Request $request,
        Comment $comment,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $response = ['status' => 'ok'];

        return $this->json($response);
        if ($this->isCsrfTokenValid(
            'delete'.$comment->getId(),
            $request->request->get('_token')
        )
        ) {
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('comment_index');
    }

    #[Route(path: '/getagentids', name: 'comment_agent_ids', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
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
