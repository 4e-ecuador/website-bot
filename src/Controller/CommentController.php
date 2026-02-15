<?php

namespace App\Controller;

use App\Entity\Agent;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\AgentRepository;
use App\Repository\CommentRepository;
use App\Service\MarkdownHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommentController extends BaseController
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly MarkdownHelper $markdownHelper,
        private readonly AgentRepository $agentRepository
    ) {
    }

    #[Route(path: '/comment/', name: 'comment_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        return $this->render(
            'comment/index.html.twig',
            [
                'comments' => $this->commentRepository->findAll(),
            ]
        );
    }

    #[Route(path: '/comment/new', name: 'comment_new', methods: [
        'GET',
        'POST',
    ])]
    #[IsGranted('ROLE_EDITOR')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
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
                'form'    => $form,
            ]
        );
    }

    #[Route(path: '/comment/fetch', name: 'comment_fetch', methods: [
        'GET',
        'POST',
    ])]
    #[IsGranted('ROLE_EDITOR')]
    public function getSingle(
        Request $request
    ): JsonResponse {
        $commentId = $request->request->get('comment_id');
        $comment = $this->commentRepository->findOneBy(['id' => $commentId]);
        if (!$comment instanceof Comment) {
            throw $this->createNotFoundException();
        }

        $comment->setText($this->markdownHelper->parse($comment->getText()));
        $html = $this->renderView(
            'comment/commentBox.html.twig',
            [
                'comment' => $comment,
            ]
        );

        return $this->json(['comment' => $html]);
    }

    #[Route(path: '/comment/{id}', name: 'comment_show', requirements: ['id' => '\d+'], methods: ['GET'])]
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

    #[Route(path: '/comment/{id}/edit', name: 'comment_edit', methods: [
        'GET',
        'POST',
    ])]
    #[IsGranted('ROLE_EDITOR')]
    public function edit(
        Request $request,
        Comment $comment,
        EntityManagerInterface $entityManager
    ): Response {
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
                'form'    => $form,
            ]
        );
    }

    #[Route(path: '/comment/{id}', name: 'comment_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_EDITOR')]
    public function delete(
        Request $request,
        Comment $comment,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete'.$comment->getId(),
            (string)$request->request->get('_token')
        )
        ) {
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('comment_index');
    }

    #[Route(path: '/comment/get-comments-by-agent', name: 'comments_by_agent', methods: ['POST'])]
    #[IsGranted('ROLE_AGENT')]
    public function getAgentCommentIds(
        Request $request
    ): JsonResponse {
        $html = '';
        $agentId = $request->request->get('agent_id');
        $agent = $this->agentRepository->findOneBy(['id' => $agentId]);
        if (!$agent instanceof Agent) {
            return $this->json(['comments' => $html]);
        }

        foreach ($agent->getComments() as $comment) {
            $comment->setText(
                $this->markdownHelper->parse($comment->getText())
            );

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
