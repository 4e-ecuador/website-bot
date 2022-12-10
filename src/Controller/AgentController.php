<?php

namespace App\Controller;

use App\Entity\Agent;
use App\Entity\Comment;
use App\Form\AgentType;
use App\Helper\Paginator\PaginatorTrait;
use App\Repository\AgentRepository;
use App\Repository\FactionRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function count;

#[Route(path: '/agent')]
class AgentController extends BaseController
{
    use PaginatorTrait;

    #[Route(path: '/', name: 'agent_index', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_AGENT')]
    public function index(): Response
    {
        // This is s Vue View ;)
        return $this->render('agent/index.html.twig');
    }

    #[Route(path: '/old', name: 'agent_index_old', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_AGENT')]
    public function indexOLD(
        AgentRepository $agentRepository,
        FactionRepository $factionRepository,
        Request $request
    ): Response {
        $paginatorOptions = $this->getPaginatorOptions($request);
        $agents = $agentRepository->getPaginatedList($paginatorOptions);
        $paginatorOptions->setMaxPages(
            (int)ceil(count($agents) / $paginatorOptions->getLimit())
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

    #[Route(path: '/new', name: 'agent_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_EDITOR')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $agent = new Agent();
        $agent->setLat($this->getParameter('app.default_lat'));
        $agent->setLon($this->getParameter('app.default_lon'));
        $form = $this->createForm(AgentType::class, $agent);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $secret = bin2hex(random_bytes(24));
            $agent->setTelegramConnectionSecret($secret);

            $entityManager->persist($agent);
            $entityManager->flush();

            return $this->redirectToRoute('agent_index');
        }

        return $this->render(
            'agent/new.html.twig',
            [
                'agent' => $agent,
                'form'  => $form,
            ]
        );
    }

    /**
     * @throws NonUniqueResultException
     */
    #[Route(path: '/{id}', name: 'agent_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_AGENT')]
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

    #[Route(path: '/{id}/edit', name: 'agent_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_EDITOR')]
    public function edit(
        Request $request,
        Agent $agent,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(AgentType::class, $agent);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

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
                'form'  => $form,
            ]
        );
    }

    #[Route(path: '/{id}', name: 'agent_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_EDITOR')]
    public function delete(
        Request $request,
        Agent $agent,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete'.$agent->getId(),
            (string)$request->request->get('_token')
        )
        ) {
            $entityManager->remove($agent);
            $entityManager->flush();
        }

        return $this->redirectToRoute('agent_index');
    }

    #[Route(path: '/{id}/add_comment', name: 'agent_add_comment', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    public function addComment(
        Request $request,
        Agent $agent,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        if ($this->isCsrfTokenValid(
            'addcomment'.$agent->getId(),
            (string)$request->request->get('_token')
        )
        ) {
            $commenter = $userRepository->findOneBy(
                ['id' => (int)$request->request->get('commenter')]
            );

            if (!$commenter) {
                return $this->json(['error' => 'invalid commenter']);
            }

            $text = (string)$request->request->get('comment');

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

    #[Route(path: '/lookup', name: 'agent_lookup', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    public function lookup(
        AgentRepository $agentRepository,
        Request $request
    ): JsonResponse {
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

    #[Route(path: '/jsonlist', name: 'json_lookup_agents', methods: ['GET'])]
    #[IsGranted('ROLE_AGENT')]
    public function agentsListJson(
        AgentRepository $agentRepository,
        Request $request
    ): JsonResponse {
        $page = $request->query->getInt('page', 1);
        $paginatorOptions = [
            'page'     => $page,
            'criteria' => [
                'nickname' => $request->query->get('nickname', ''),
            ],
        ];
        $modRequest = clone $request;
        $modRequest->query->set('paginatorOptions', $paginatorOptions);

        $paginatorOptions = $this->getPaginatorOptions($modRequest);
        /**
         * @var Agent[] $agents
         */
        $agents = $agentRepository->getPaginatedList($paginatorOptions);
        $paginatorOptions->setMaxPages(
            (int)ceil(count($agents) / $paginatorOptions->getLimit())
        );

        $list = [];

        foreach ($agents as $agent) {
            $list[] = [
                'id'       => $agent->getId(),
                'nickname' => $agent->getNickname(),
                'realName' => $agent->getRealName(),
            ];
        }

        $response = new \stdClass();

        $response->{'hydra:member'} = $list;

        $view = new \stdClass();
        $view->{'hydra:previous'} = ($page > 1) ? 'X' : null;
        $view->{'hydra:next'} = ($page < $paginatorOptions->getMaxPages()) ? 'X'
            : null;
        $view->{'hydra:last'} = $paginatorOptions->getMaxPages();

        $response->{'hydra:view'} = $view;
        $response->{'hydra:totalItems'} = count($agents);

        return $this->json($response);
    }
}
