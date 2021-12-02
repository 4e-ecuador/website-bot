<?php

namespace App\Controller;

use App\Entity\AgentStat;
use App\Form\AgentStatType;
use App\Helper\Paginator\PaginatorTrait;
use App\Repository\AgentRepository;
use App\Repository\AgentStatRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function count;

#[Route(path: '/agent-stat')]
class AgentStatController extends BaseController
{
    use PaginatorTrait;
    /**
     * @IsGranted("ROLE_AGENT")
     */
    #[Route(path: '/', name: 'agent_stat_index', methods: ['GET', 'POST'])]
    public function index(AgentStatRepository $agentStatRepository, AgentRepository $agentRepository, Request $request): Response
    {
        $paginatorOptions = $this->getPaginatorOptions($request);
        $stats = $agentStatRepository->getPaginatedList($paginatorOptions);
        $paginatorOptions->setMaxPages(
            ceil(count($stats) / $paginatorOptions->getLimit())
        );
        $agents = [];
        $agents[0] = '';
        foreach ($agentRepository->findAllAlphabetical() as $item) {
            $agents[$item->getId()] = $item->getNickname();
        }
        return $this->render(
            'agent_stat/index.html.twig',
            [
                'agent_stats'      => $stats,
                'paginatorOptions' => $paginatorOptions,
                'agents'           => $agents,
            ]
        );
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route(path: '/new', name: 'agent_stat_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $agentStat = new AgentStat();
        $form = $this->createForm(AgentStatType::class, $agentStat);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
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
    /**
     * @IsGranted("ROLE_AGENT")
     */
    #[Route(path: '/{id}', name: 'agent_stat_show', methods: ['GET'])]
    public function show(AgentStat $agentStat): Response
    {
        return $this->render(
            'agent_stat/show.html.twig',
            [
                'agent_stat' => $agentStat,
            ]
        );
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route(path: '/{id}/edit', name: 'agent_stat_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AgentStat $agentStat): Response
    {
        $form = $this->createForm(AgentStatType::class, $agentStat);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

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
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route(path: '/{id}', name: 'agent_stat_delete', methods: ['DELETE'])]
    public function delete(Request $request, AgentStat $agentStat): Response
    {
        if ($this->isCsrfTokenValid(
            'delete'.$agentStat->getId(),
            $request->request->get('_token')
        )
        ) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($agentStat);
            $entityManager->flush();
        }
        return $this->redirectToRoute('agent_stat_index');
    }
}
