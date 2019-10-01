<?php

namespace App\Controller;

use App\Entity\Agent;
use App\Repository\AgentRepository;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MapController extends AbstractController
{
    /**
     * @Route("/map", name="agent-map")
     */
    public function map(
        AgentRepository $agentRepository,
        CommentRepository $commentRepository
    ): Response {
        return $this->render(
            'map/index.html.twig',
            [
                'agents'         => $agentRepository->findAll(),
                'latestComments' => $commentRepository->findLatestTen(),
            ]
        );
    }

    /**
     * @Route("/map_json", name="map-json")
     */
    public function mapJson(AgentRepository $agentRepository): JsonResponse
    {
        $agents = $agentRepository->findMapAgents();

        $array = [];

        foreach ($agents as $agent) {
            $a = [];

            $a['name'] = $agent->getNickname();
            $a['lat']  = $agent->getLat();
            $a['lng']  = $agent->getLon();
            $a['id']   = $agent->getId();

            $array[] = $a;
        }

        return $this->json($array);
    }

    /**
     * @Route("/map/agent-info/{id}", name="agent-info")
     */
    public function mapAgentInfo(Agent $agent): Response
    {
        $response = [];

        if ($this->isGranted('ROLE_ADMIN')) {
            $response[] = $agent->getNickname();
        } else {
            $response[] = 'Please log in';
        }

        return new Response(implode("\n", $response));
    }
}
