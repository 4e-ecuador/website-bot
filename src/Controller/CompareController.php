<?php

namespace App\Controller;

use App\Repository\AgentRepository;
use App\Repository\AgentStatRepository;
use App\Repository\UserRepository;
use App\Service\LeaderBoardService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_AGENT")
 */
class CompareController extends AbstractController
{
    #[Route('/compare', name: 'compare')]
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

    #[Route('/compare-preview', name: 'compare_preview')]
    public function preview(
        Request $request,
        AgentRepository $agentRepository
    ): Response {
        $searchTerm = $request->query->get('q');
        try {
            $excludes = json_decode(
                $request->query->get('excludes'),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
            $excludes = \array_map(static fn(string $id): int => (int) $id, $excludes);
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

    #[Route('/compare-agent-list', name: 'compare_agent_list')]
    public function agentList(
        Request $request,
        AgentRepository $agentRepository,
        UserRepository $userRepository,
        AgentStatRepository $agentStatRepository,
        LeaderBoardService $leaderBoardService
    ): Response {
        $board = [];
        try {
            $ids = json_decode(
                $request->query->get('agents'),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
            $ids = \array_map(static fn(string $id): int => (int) $id, $ids);
            $stats = [];
            if (count($ids) > 1) {
                $users = [];
                foreach ($ids as $id) {
                    $agent = $agentRepository->find($id);
                    if ($agent) {
                        $user = $userRepository->findByAgent($agent);
                        if ($user) {
                            $users[] = $user;
                        }
                    }
                    $s = new \stdClass();
                    $s->agent = $agent;
                    $s->data = $agentStatRepository->getAgentLatest($agent);

                    $stats[] = $s;
                }
                if (count($users) > 1) {
                    $board = $leaderBoardService->getBoard($users);
                }
            }
        } catch (\Exception $exception) {
            return new Response($exception->getMessage());
        }

        return $this->render(
            'compare/_agent_list.html.twig',
            [
                'agents' => $agentRepository->searchByIds($ids),
                'stats'  => $stats,
                'board'  => $board,
            ]
        );
    }

    #[Route('/compare-result', name: 'compare_result')]
    public function compareResult(
        Request $request,
        AgentRepository $agentRepository,
        UserRepository $userRepository,
        AgentStatRepository $agentStatRepository,
        LeaderBoardService $leaderBoardService
    ): Response {
        $board = [];
        try {
            $ids = json_decode(
                $request->query->get('agents'),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
            $ids = \array_map(static fn(string $id): int => (int) $id, $ids);
            $stats = [];
            if (count($ids) > 1) {
                $users = [];
                foreach ($ids as $id) {
                    $agent = $agentRepository->find($id);
                    if ($agent) {
                        $user = $userRepository->findByAgent($agent);
                        if ($user) {
                            $users[] = $user;
                        }
                    }
                    $s = new \stdClass();
                    $s->agent = $agent;
                    $s->data = $agentStatRepository->getAgentLatest($agent);

                    $stats[] = $s;
                }
                if (count($users) > 1) {
                    $board = $leaderBoardService->getBoard($users);
                }
            }
        } catch (\Exception $exception) {
            return new Response($exception->getMessage());
        }

        return $this->render(
            'compare/_result.html.twig',
            [
                'agents' => $agentRepository->searchByIds($ids),
                'stats'  => $stats,
                'board'  => $board,
            ]
        );
    }
}
