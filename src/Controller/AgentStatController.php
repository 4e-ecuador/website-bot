<?php

namespace App\Controller;

use App\Entity\AgentStat;
use App\Form\AgentStatType;
use App\Helper\Paginator\PaginatorTrait;
use App\Repository\AgentRepository;
use App\Repository\AgentStatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function count;

class AgentStatController extends BaseController
{
    use PaginatorTrait;

    public function __construct(
        private readonly AgentStatRepository $agentStatRepository,
        private readonly AgentRepository $agentRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route(path: '/agent-stat/', name: 'agent_stat_index', methods: [
        'GET',
        'POST',
    ])]
    #[IsGranted('ROLE_AGENT')]
    public function index(
        Request $request
    ): Response {
        $paginatorOptions = $this->getPaginatorOptions($request);
        $stats = $this->agentStatRepository->getPaginatedList(
            $paginatorOptions
        );
        $paginatorOptions->setMaxPages(
            (int)ceil(count($stats) / $paginatorOptions->getLimit())
        );
        $agents = [];
        $agents[0] = '';
        foreach ($this->agentRepository->findAllAlphabetical() as $item) {
            $agents[$item->getId()] = $item->getNickname();
        }

        return $this->render(
            'agent_stat/index.html.twig',
            [
                'agent_stats' => $stats,
                'paginatorOptions' => $paginatorOptions,
                'agents' => $agents,
            ]
        );
    }

    #[Route(path: '/agent-stat/new', name: 'agent_stat_new', methods: [
        'GET',
        'POST',
    ])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(
        Request $request
    ): Response {
        $agentStat = new AgentStat();
        $form = $this->createForm(AgentStatType::class, $agentStat);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($agentStat);
            $this->entityManager->flush();

            return $this->redirectToRoute('agent_stat_index');
        }

        return $this->render(
            'agent_stat/new.html.twig',
            [
                'agent_stat' => $agentStat,
                'form'       => $form,
            ]
        );
    }

    #[Route(path: '/agent-stat/{id}', name: 'agent_stat_show', methods: ['GET'])]
    #[IsGranted('ROLE_AGENT')]
    public function show(AgentStat $agentStat): Response
    {
        return $this->render(
            'agent_stat/show.html.twig',
            [
                'agent_stat' => $agentStat,
            ]
        );
    }

    #[Route(path: '/agent-stat/{id}/edit', name: 'agent_stat_edit', methods: [
        'GET',
        'POST',
    ])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        Request $request,
        AgentStat $agentStat
    ): Response {
        $form = $this->createForm(AgentStatType::class, $agentStat);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('agent_stat_index');
        }

        return $this->render(
            'agent_stat/edit.html.twig',
            [
                'agent_stat' => $agentStat,
                'form'       => $form,
            ]
        );
    }

    #[Route(path: '/agent-stat/{id}', name: 'agent_stat_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        Request $request,
        AgentStat $agentStat
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete'.$agentStat->getId(),
            (string)$request->request->get('_token')
        )
        ) {
            $this->entityManager->remove($agentStat);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('agent_stat_index');
    }
}
