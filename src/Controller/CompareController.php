<?php

namespace App\Controller;

use App\Repository\AgentRepository;
use App\Repository\UserRepository;
use App\Service\LeaderBoardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_AGENT')]
class CompareController extends AbstractController
{
    #[Route('/compare', name: 'compare', methods: ['GET'])]
    public function index(
        Request $request
    ): Response {
        $searchTerm = $request->query->get('q');

        return $this->render(
            'compare/index.html.twig',
            [
                'searchTerm' => $searchTerm,
            ]
        );
    }

    #[Route('/compare-preview', name: 'compare_preview', methods: ['GET'])]
    public function preview(
        Request $request,
        AgentRepository $agentRepository
    ): Response {
        $searchTerm = $request->query->get('q');
        try {
            $excludes = $this->getIdsFromRequest($request, 'excludes');
        } catch (\JsonException $e) {
            return new Response($e->getMessage());
        }

        return $this->render(
            'compare/_preview.html.twig',
            [
                'agents' => $agentRepository->searchByAgentName(
                    $searchTerm,
                    $excludes
                ),
            ]
        );
    }

    #[Route('/compare-agent-list', name: 'compare_agent_list', methods: ['GET'])]
    public function agentList(
        Request $request,
        AgentRepository $agentRepository,
    ): Response {
        try {
            $ids = $this->getIdsFromRequest($request);
        } catch (\Exception $exception) {
            return new Response($exception->getMessage());
        }

        return $this->render(
            'compare/_agent_list.html.twig',
            [
                'agents' => $agentRepository->searchByIds($ids),
            ]
        );
    }

    #[Route('/compare-result', name: 'compare_result', methods: ['GET'])]
    public function compareResult(
        Request $request,
        AgentRepository $agentRepository,
        UserRepository $userRepository,
        LeaderBoardService $leaderBoardService
    ): Response {
        try {
            $ids = $this->getIdsFromRequest($request);
            $users = [];
            foreach ($ids as $id) {
                $agent = $agentRepository->find($id);
                if ($agent) {
                    $user = $userRepository->findByAgent($agent);
                    if ($user) {
                        $users[] = $user;
                    }
                }
            }
            $board = $leaderBoardService->getBoard($users);
        } catch (\Exception $exception) {
            return new Response($exception->getMessage());
        }

        return $this->render(
            'compare/_result.html.twig',
            [
                'board' => $board,
            ]
        );
    }

    /**
     * @return int[]
     * @throws \JsonException
     */
    private function getIdsFromRequest(
        Request $request,
        string $key = 'agents'
    ): array {
        $ids = json_decode(
            $request->query->get($key),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (!is_array($ids)) {
            return [];
        }

        return \array_map(static fn(string $id): int => (int)$id, $ids);
    }
}
