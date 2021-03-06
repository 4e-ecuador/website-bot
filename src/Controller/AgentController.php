<?php

namespace App\Controller;

use App\Entity\Agent;
use App\Entity\Comment;
use App\Form\AgentType;
use App\Helper\Paginator\PaginatorTrait;
use App\Repository\AgentRepository;
use App\Repository\FactionRepository;
use App\Repository\UserRepository;
use App\Service\MailerHelper;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function count;

#[Route(path: '/agent')]
class AgentController extends AbstractController
{
    use PaginatorTrait;
    /**
     * @IsGranted("ROLE_AGENT")
     */
    #[Route(path: '/', name: 'agent_index', methods: ['GET'])]
    public function index(): Response
    {
        // Tis is s Vue View ;)
        return $this->render('agent/index.html.twig');
    }
    /**
     * @IsGranted("ROLE_AGENT")
     */
    #[Route(path: '/old', name: 'agent_index_old', methods: ['GET', 'POST'])]
    public function indexOLD(AgentRepository $agentRepository, FactionRepository $factionRepository, Request $request): Response
    {
        $paginatorOptions = $this->getPaginatorOptions($request);
        $agents = $agentRepository->getPaginatedList($paginatorOptions);
        $paginatorOptions->setMaxPages(
            ceil(count($agents) / $paginatorOptions->getLimit())
        );
        $factions = $factionRepository->findAll();
        $factionList = [];
        foreach ($factions as $faction) {
            $factionList[$faction->getId()] = $faction->getName();
        }
        return $this->render(
            'agent/index_old.html.twig',
            [
                'agents'           => $agents,
                'paginatorOptions' => $paginatorOptions,
                'factions'         => $factionList,
            ]
        );
    }
    /**
     * @IsGranted("ROLE_EDITOR")
     * @throws Exception
     */
    #[Route(path: '/new', name: 'agent_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $agent = new Agent();
        $agent->setLat($this->getParameter('app.default_lat'));
        $agent->setLon($this->getParameter('app.default_lon'));
        $form = $this->createForm(AgentType::class, $agent);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $secret = bin2hex(random_bytes(24));
            $agent->setTelegramConnectionSecret($secret);

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
     * @IsGranted("ROLE_AGENT")
     * @throws NonUniqueResultException
     */
    #[Route(path: '/{id}', name: 'agent_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Agent $agent, UserRepository $userRepository): Response
    {
        return $this->render(
            'agent/show.html.twig',
            [
                'agent' => $agent,
                'user'  => $userRepository->findByAgent($agent),
            ]
        );
    }
    /**
     * @IsGranted("ROLE_EDITOR")
     */
    #[Route(path: '/{id}/edit', name: 'agent_edit', methods: ['GET', 'POST'])]
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
     * @IsGranted("ROLE_EDITOR")
     */
    #[Route(path: '/{id}', name: 'agent_delete', methods: ['DELETE'])]
    public function delete(Request $request, Agent $agent): Response
    {
        if ($this->isCsrfTokenValid(
            'delete'.$agent->getId(),
            $request->request->get('_token')
        )
        ) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($agent);
            $entityManager->flush();
        }
        return $this->redirectToRoute('agent_index');
    }
    /**
     * @IsGranted("ROLE_EDITOR")
     */
    #[Route(path: '/{id}/add_comment', name: 'agent_add_comment', methods: ['POST'])]
    public function addComment(Request $request, Agent $agent, UserRepository $userRepository, MailerHelper $mailerHelper): JsonResponse
    {
        if ($this->isCsrfTokenValid(
            'addcomment'.$agent->getId(),
            $request->request->get('_token')
        )
        ) {
            $entityManager = $this->getDoctrine()->getManager();

            $commenter = $userRepository->findOneBy(
                ['id' => (int)$request->request->get('commenter')]
            );

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
                ->setDatetime(new DateTime());

            $entityManager->persist($comment);
            $entityManager->flush();

            $response = [
                'id' => $comment->getId(),
            ];

            // $mailerHelper->sendNewCommentMail($comment);

            return $this->json($response);
        }
        return $this->json(['error' => 'error']);
    }
    /**
     * @IsGranted("ROLE_EDITOR")
     */
    #[Route(path: '/lookup', name: 'agent_lookup', methods: ['POST'])]
    public function lookup(AgentRepository $agentRepository, Request $request): JsonResponse
    {
        $query = $request->query->get('query');
        $list = [];
        $results = $agentRepository->searchByAgentName($query);
        foreach ($results as $result) {
            $list[] = [
                'name'    => $result->getNickname(),
                'faction' => $result->getFaction()->getName(),
            ];
        }
        return $this->json($list);
    }
}
