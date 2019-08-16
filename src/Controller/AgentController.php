<?php

namespace App\Controller;

use App\Entity\Agent;
use App\Entity\Comment;
use App\Form\AgentType;
use App\Form\CommentInlineType;
use App\Form\CommentType;
use App\Repository\AgentRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/agent")
 * @IsGranted("ROLE_ADMIN")
 */
class AgentController extends AbstractController
{
    /**
     * @Route("/", name="agent_index", methods={"GET"})
     */
    public function index(AgentRepository $agentRepository): Response
    {
        return $this->render(
            'agent/index.html.twig',
            [
                'agents' => $agentRepository->findAll(),
            ]
        );
    }

    /**
     * @Route("/new", name="agent_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $agent = new Agent();
        $form  = $this->createForm(AgentType::class, $agent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($agent);
            $entityManager->flush();

            return $this->redirectToRoute('agent_index');
        }

        return $this->render(
            'agent/new.html.twig',
            [
                'agent' => $agent,
                'form'  => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="agent_show", methods={"GET"})
     */
    public function show(Agent $agent, Security $security): Response
    {
//        $comment = new Comment();
//        $comment->setAgent($agent);
//        $comment->setCommenter($security->getUser());
//        $comment->setDatetime(new \DateTime());
//
//        $form = $this->createForm(CommentInlineType::class, $comment);

        return $this->render(
            'agent/show.html.twig',
            [
                'agent' => $agent,
//                'form'  => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="agent_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Agent $agent): Response
    {
        $form = $this->createForm(AgentType::class, $agent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute(
                'agent_index',
                [
                    'id' => $agent->getId(),
                ]
            );
        }

        return $this->render(
            'agent/edit.html.twig',
            [
                'agent' => $agent,
                'form'  => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="agent_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Agent $agent): Response
    {
        if ($this->isCsrfTokenValid('delete'.$agent->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($agent);
            $entityManager->flush();
        }

        return $this->redirectToRoute('agent_index');
    }

    /**
     * @Route("/{id}/add_comment", name="agent_add_comment", methods={"POST"})
     */
    public function addComment(Request $request, Agent $agent, UserRepository $userRepository): JsonResponse
    {
        if ($this->isCsrfTokenValid('addcomment'.$agent->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();

            $commenter = $userRepository->findOneBy(['id' => (int)$request->request->get('commenter')]);
            if (!$commenter) {
                return $this->json(['error' => 'invalid commenter']);
            }

            $text = $request->request->get('comment');

            if (!$text) {
                return $this->json(['error' => 'no comment...']);
            }


            $comment = new Comment();
            $comment->setCommenter($commenter)
                ->setAgent($agent)
                ->setText($text)
                ->setDatetime(new \DateTime());

            $entityManager->persist($comment);
            $entityManager->flush();

            $response = [
                'id' => $comment->getId(),
            ];

            return $this->json($response);
        }


        return $this->json(['error' => 'error']);
    }
}
