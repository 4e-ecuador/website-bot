<?php

namespace App\Controller;

use App\Entity\AgentStat;
use App\Form\AgentStatType;
use App\Helper\Paginator\PaginatorTrait;
use App\Repository\AgentRepository;
use App\Repository\AgentStatRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/agent-stat")
 */
class AgentStatController extends AbstractController
{
    use PaginatorTrait;

    /**
     * @Route("/", name="agent_stat_index", methods={"GET","POST"})
     * @IsGranted("ROLE_AGENT")
     */
    public function index(AgentStatRepository $agentStatRepository, Request $request): Response
    {
        $paginatorOptions = $this->getPaginatorOptions($request);

        $stats = $agentStatRepository->getPaginatedList($paginatorOptions);

        $paginatorOptions->setMaxPages(
            ceil(\count($stats) / $paginatorOptions->getLimit())
        );

        return $this->render(
            'agent_stat/index.html.twig',
            [
                'agent_stats' => $stats,
                'paginatorOptions' => $paginatorOptions,
            ]
        );
    }

    /**
     * @Route("/new", name="agent_stat_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request): Response
    {
        $agentStat = new AgentStat();
        $form      = $this->createForm(AgentStatType::class, $agentStat);
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
     * @Route("/{id}", name="agent_stat_show", methods={"GET"})
     * @IsGranted("ROLE_AGENT")
     */
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
     * @Route("/{id}/edit", name="agent_stat_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
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
     * @Route("/{id}", name="agent_stat_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
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
