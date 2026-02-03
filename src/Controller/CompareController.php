<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\AgentRepository;
use App\Repository\UserRepository;
use App\Service\LeaderBoardService;
use Exception;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function array_map;

#[IsGranted('ROLE_AGENT')]
class CompareController extends AbstractController
{
    public function __construct(
        private readonly AgentRepository $agentRepository,
        private readonly UserRepository $userRepository,
        private readonly LeaderBoardService $leaderBoardService
    ) {
    }

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
        Request $request
    ): Response {
        $searchTerm = $request->query->get('q');
        try {
            $excludes = $this->getIdsFromRequest($request, 'excludes');
        } catch (JsonException $jsonException) {
            return new Response($jsonException->getMessage());
        }

        return $this->render(
            'compare/_preview.html.twig',
            [
                'agents' => $this->agentRepository->searchByAgentName(
                    $searchTerm,
                    $excludes
                ),
            ]
        );
    }

    #[Route('/compare-agent-list', name: 'compare_agent_list', methods: ['GET'])]
    public function agentList(
        Request $request,
    ): Response {
        try {
            $ids = $this->getIdsFromRequest($request);
        } catch (Exception $exception) {
            return new Response($exception->getMessage());
        }

        return $this->render(
            'compare/_agent_list.html.twig',
            [
                'agents' => $this->agentRepository->searchByIds($ids),
            ]
        );
    }

    #[Route('/compare-result', name: 'compare_result', methods: ['GET'])]
    public function compareResult(
        Request $request
    ): Response {
        try {
            $ids = $this->getIdsFromRequest($request);
            $users = [];
            foreach ($ids as $id) {
                $agent = $this->agentRepository->find($id);
                if ($agent) {
                    $user = $this->userRepository->findByAgent($agent);
                    if ($user instanceof User) {
                        $users[] = $user;
                    }
                }
            }

            $board = $this->leaderBoardService->getBoard($users);
        } catch (Exception $exception) {
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
     * @throws JsonException
     */
    private function getIdsFromRequest(
        Request $request,
        string $key = 'agents'
    ): array {
        $ids = json_decode(
            (string)$request->query->get($key),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (!is_array($ids)) {
            return [];
        }

        return array_map(static fn(string $id): int => (int)$id, $ids);
    }
}
