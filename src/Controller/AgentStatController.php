<?php

namespace App\Controller;

use App\Entity\AgentStat;
use App\Form\AgentStatType;
use App\Helper\Paginator\PaginatorTrait;
use App\Repository\AgentRepository;
use App\Repository\AgentStatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function count;

#[Route(path: '/agent-stat')]
class AgentStatController extends BaseController
{
    use PaginatorTrait;

    #[Route(path: '/', name: 'agent_stat_index', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_AGENT')]
    public function index(
        AgentStatRepository $agentStatRepository,
        AgentRepository $agentRepository,
        Request $request
    ): Response {
        $paginatorOptions = $this->getPaginatorOptions($request);
        $stats = $agentStatRepository->getPaginatedList($paginatorOptions);
        $paginatorOptions->setMaxPages(
            (int)ceil(count($stats) / $paginatorOptions->getLimit())
        );
        $agents = [];
        $agents[0] = '';
        foreach ($agentRepository->findAllAlphabetical() as $item) {
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

    #[Route(path: '/new', name: 'agent_stat_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $agentStat = new AgentStat();
        $form = $this->createForm(AgentStatType::class, $agentStat);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($agentStat);
            $entityManager->flush();

            return $this->redirectToRoute('agent_stat_index');
        }

        return $this->render(
            'agent_stat/new.html.twig',
            [
                'agent_stat' => $agentStat,
                'form'       => $form->createView(),
            ]
        );
    }

    #[Route(path: '/{id}', name: 'agent_stat_show', methods: ['GET'])]
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

    #[Route(path: '/{id}/edit', name: 'agent_stat_edit', methods: [
        'GET',
        'POST',
    ])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, AgentStat $agentStat, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AgentStatType::class, $agentStat);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('agent_stat_index');
        }

        return $this->render(
            'agent_stat/edit.html.twig',
            [
                'agent_stat' => $agentStat,
                'form'       => $form->createView(),
            ]
        );
    }

    #[Route(path: '/{id}', name: 'agent_stat_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, AgentStat $agentStat, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid(
            'delete'.$agentStat->getId(),
            (string)$request->request->get('_token')
        )
        ) {
            $entityManager->remove($agentStat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('agent_stat_index');
    }
}
