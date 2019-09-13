<?php

namespace App\Controller;

use App\Entity\Agent;
use App\Entity\Comment;
use App\Form\AgentType;
use App\Helper\Paginator\PaginatorTrait;
use App\Repository\AgentRepository;
use App\Repository\UserRepository;
use App\Service\CsvParser;
use App\Service\MedalChecker;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/agent")
 */
class AgentController extends AbstractController
{
    use PaginatorTrait;

    /**
     * @Route("/", name="agent_index", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(AgentRepository $agentRepository, Request $request): Response
    {
        $paginatorOptions = $this->getPaginatorOptions($request);

        $agents = $agentRepository->getPaginatedList($paginatorOptions);

        $paginatorOptions->setMaxPages(ceil(\count($agents) / $paginatorOptions->getLimit()));

        return $this->render(
            'agent/index.html.twig',
            [
                'agents'           => $agents,
                'paginatorOptions' => $paginatorOptions,
            ]
        );
    }

    /**
     * @Route("/new", name="agent_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
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
     * @Route("/{id}", name="agent_show", methods={"GET"}, requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN")
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
     * @IsGranted("ROLE_ADMIN")
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
     * @IsGranted("ROLE_ADMIN")
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
     * @IsGranted("ROLE_ADMIN")
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

    /**
     * @Route("/test-stat-import", name="test_stat_import", methods={"POST", "GET"})
     */
    public function testStatImport(Request $request, CsvParser $csvParser, MedalChecker $medalChecker, AgentRepository $agentRepository): Response
    {
        $csv = $request->get('csv');
        $importType = $request->get('type');
        $ups = [];
        $currents = [];

        if ($csv) {
            try {
                $parsed = $csvParser->parse($csv, $importType);

                $agent = $agentRepository->findOneBy(['id' => 66]);

                foreach ($parsed as $date => $values) {
                    $statEntries = $medalChecker->checkLevels($values);

                    if (!$currents) {
                        $currents = $statEntries;

                        continue;
                    }

                    foreach ($currents as $name => $currentVal) {
                        $newVal = $statEntries[$name];
                        if ($newVal > $currentVal) {
                            $ups[$date]['agentX'][$name] = $newVal;
                            $currents[$name]             = $newVal;
                        }
                    }
                }
            } catch (\UnexpectedValueException $exception) {
                $this->addFlash('danger', $exception->getMessage());
            }
        }

        return $this->render(
            'agent/testimport.html.twig',
            [
                'ups' => $ups,
                'currents' => $currents,
            ]
        );
    }
}
