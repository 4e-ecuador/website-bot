<?php

namespace App\Controller;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Entity\User;
use App\Exception\StatsAlreadyAddedException;
use App\Exception\StatsNotAllException;
use App\Repository\AgentStatRepository;
use App\Repository\UserRepository;
use App\Service\LeaderBoardService;
use App\Service\MedalChecker;
use App\Service\StatsImporter;
use App\Type\BoardEntry;
use App\Type\ImportResult;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use JsonException;
use stdClass;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use UnexpectedValueException;

class StatsController extends BaseController
{
    public function __construct(
        private readonly AgentStatRepository $statRepository,
        private readonly UserRepository $userRepository,
        private readonly MedalChecker $medalChecker,
        private readonly LeaderBoardService $leaderBoardService,
        private readonly AgentStatRepository $repository,
        private readonly StatsImporter $statsImporter,
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * @throws NonUniqueResultException
     */
    #[Route(path: '/stats/agent/{id}', name: 'agent_stats', methods: ['GET'])]
    #[IsGranted('ROLE_AGENT')]
    public function agentStats(
        Agent $agent
    ): Response {
        $medalGroups = [];
        $latest = $this->statRepository->getAgentLatest($agent);
        if ($latest instanceof AgentStat) {
            $medals = $this->medalChecker->checkLevels($latest);
            arsort($medals);
            $medalGroups = $medals;
        }

        $dateEnd = new DateTime();
        $dateStart = new DateTime()->sub(new DateInterval('P30D'));
        try {
            $customMedals = json_decode(
                (string)$agent->getCustomMedals(),
                true,
                512,
                JSON_THROW_ON_ERROR | JSON_ERROR_NONE
            );
        } catch (JsonException) {
            $customMedals = '';
        }

        return $this->render(
            'stats/agent-stats.html.twig',
            [
                'agent'       => $agent,
                'user'        => $this->userRepository->findByAgent($agent),
                'medalGroups' => $medalGroups,
                'latest'      => $latest,
                'dateStart'   => $dateStart,
                'dateEnd'     => $dateEnd,
                'first'       => $this->statRepository->getAgentLatest(
                    $agent,
                    true
                ),

                'agentCustomMedals' => $customMedals,
            ]
        );
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/stats/agent/data/{id}/{startDate}/{endDate}', name: 'agent_stats_data', methods: ['GET'])]
    #[IsGranted('ROLE_INTRO_AGENT')]
    public function agentStatsJson(
        Agent $agent,
        string $startDate,
        string $endDate
    ): JsonResponse {
        $data = new stdClass();
        $data->ap = [];
        $data->hacker = [];

        $entries = $this->statRepository->findByDateAndAgent(
            new DateTime($startDate),
            new DateTime($endDate),
            $agent
        );
        foreach ($entries as $entry) {
            // Get the correct datetime format for highcharts
            // See: https://stackoverflow.com/a/29234143/1906767
            $date = (int)($entry->getDatetime()?->format('U')) * 1000;
            $data->ap[] = [$date, $entry->getAp()];
            $data->hacker[] = [$date, $entry->getHacker()];
        }

        return new JsonResponse($data);
    }

    #[Route(path: '/stats/leaderboard', name: 'stats_leaderboard', methods: ['GET'])]
    #[IsGranted('ROLE_AGENT')]
    public function leaderBoard(): Response
    {
        return $this->render(
            'stats/leaderboard.html.twig',
            [
                'board'    => $this->getBoardEntries(
                    $this->userRepository,
                    $this->leaderBoardService,
                ),
                'cssClass' => 'col-sm-3 ',
            ]
        );
    }

    #[Route(path: '/stats/leaderboard-detail', name: 'stats_leaderboard_detail', methods: ['POST'])]
    #[IsGranted('ROLE_AGENT')]
    public function leaderBoardDetail(
        Request $request
    ): Response {
        $item = (string)$request->request->get('item', 'ap');
        $entries = $this->getBoardEntries(
            $this->userRepository,
            $this->leaderBoardService,
            $item
        );

        return $this->render(
            'stats/_stat_entry.html.twig',
            [
                'type'     => $item,
                'entries'  => $entries,
                'maxCount' => 999999,
                'cssClass' => '',

            ]
        );
    }

    /**
     * @return array<string, array<int, BoardEntry>>|array<int, BoardEntry>
     */
    private function getBoardEntries(
        UserRepository $userRepository,
        LeaderBoardService $leaderBoardService,
        string $typeOnly = 'all'
    ): array {
        $users = $userRepository->findAll();

        return $leaderBoardService->getBoard($users, $typeOnly);
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/stats/by-date', name: 'stats_by_date', methods: ['GET'])]
    #[IsGranted('ROLE_AGENT')]
    public function byDate(
        Request $request
    ): Response {
        $startDate = $request->query->get('start_date');
        $endDate = $request->query->get('end_date');
        $stats = [];
        $medalsGained = [];
        $medalsGained1 = [];
        if ($startDate && $endDate) {
            $entries = $this->statRepository->findByDate(
                new DateTime($startDate),
                new DateTime($endDate.' 23:59:59')
            );

            $result = $this->medalChecker->getMedalsGained($entries, $this->statRepository);
            $medalsGained = $result['byDate'];
            $medalsGained1 = $result['byMedal'];
        }

        return $this->render(
            'stats/by_date.html.twig',
            [
                'startDate'     => new DateTime($startDate ?? 'now'),
                'endDate'       => new DateTime($endDate ?? 'now'),
                'stats'         => $stats,
                'medalsGained'  => $medalsGained,
                'medalsGained1' => $medalsGained1,
            ]
        );
    }

    #[Route(path: '/stats/in-between', name: 'stats_in_between', methods: ['GET'])]
    #[IsGranted('ROLE_AGENT')]
    public function inBetween(): Response
    {
        $agent = $this->getUser()?->getAgent();
        if (!$agent) {
            throw $this->createAccessDeniedException('Not an agent...');
        }

        $stats = $this->statRepository->getAgentStats($agent);
        $dates = [];
        foreach ($stats as $stat) {
            $dateString = $stat->getDatetime()?->format('Y n j H:i:s');
            [$year, $month, $day, $time] = explode(' ', (string)$dateString);
            $dates[$year][$month][$day][] = $time;
        }

        return $this->render('stats/in-between.html.twig', [
            'dates'     => $dates,
            'firstDate' => $stats[count($stats) - 1]->getDatetime()?->format(
                'Y-n-j H:i:s'
            ),
            'lastDate'  => $stats[0]->getDatetime()?->format('Y-n-j H:i:s'),
        ]);
    }

    #[Route(path: '/stats/in-between-result', name: 'stats_in_between_result', methods: [
        'POST',
        'GET',
    ])]
    #[IsGranted('ROLE_AGENT')]
    public function inBetweenResult(
        Request $request,
    ): Response {
        $dateStart = $request->query->get('dateStart');
        $dateEnd = $request->query->get('dateEnd');

        $startEntry = $this->repository->findOneBy([
            'datetime' => new DateTime($dateStart),
        ]);
        $endEntry = $this->repository->findOneBy([
            'datetime' => new DateTime($dateEnd),
        ]);

        if (!$startEntry || !$endEntry) {
            $error = 'Invalid entries';
            $result = new ImportResult();
        } else {
            $error = '';
            $result = $this->statsImporter->getImportResult(
                $endEntry,
                $startEntry
            );
        }

        return $this->render('import/_result.html.twig', [
            'statEntry' => $endEntry,
            'result'    => $result,
            'error'     => $error,
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/stats/stat-import', name: 'stat_import', methods: [
        'POST',
        'GET',
    ])]
    #[IsGranted('ROLE_INTRO_AGENT')]
    public function statImport(
        Request $request,
        EntityManagerInterface $entityManager,
        #[Autowire('%env(APP_ENV)%')] string $appEnv
    ): Response {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            throw new UnexpectedValueException('User not found');
        }

        $agent = $user->getAgent();
        if (!$agent) {
            throw $this->createAccessDeniedException(
                $this->translator->trans('user.not.verified.2')
            );
        }

        $csv = $request->request->getString('csv');
        if ($csv !== '') {
            try {
                $statEntry = $this->statsImporter
                    ->createEntryFromCsv($agent, $csv);

                $entityManager->persist($statEntry);
                $entityManager->flush();

                // TODO TEST!!
                // $testStat = (new TestStat())
                //     ->setCsv($csv);
                // $entityManager->persist($testStat);
                // $entityManager->flush();

                $this->addFlash(
                    'success',
                    $this->translator->trans('Stats upload successful!')
                );

                $result = $this->statsImporter->getImportResult($statEntry);

                try {
                    $this->statsImporter
                        ->sendResultMessages($result, $statEntry, $user);
                } catch (Exception $exception) {
                    $this->addFlash(
                        'warning',
                        $this->translator->trans(
                            'Sorry but the message has not been sent :('
                        )
                    );
                    if ('dev' === $appEnv) {
                        throw $exception;
                    }
                }

                // @TODO temporal FireBase token store
                $fireBaseToken = $request->request->getString(
                    'fire_base_token'
                );
                if ($fireBaseToken !== '' && !$user->getFireBaseToken()) {
                    $user->setFireBaseToken($fireBaseToken);
                    $entityManager->persist($user);
                    $entityManager->flush();
                }

                return $this->render(
                    'import/result.html.twig',
                    [
                        'statEntry' => $statEntry,
                        'result'    => $result,
                        'error'     => '',
                    ]

                );
            } catch (
            StatsNotAllException
            |StatsAlreadyAddedException
            |UnexpectedValueException
            $exception) {
                $this->addFlash('danger', $exception->getMessage());
            }
        }

        return $this->render('import/agent_stats.html.twig');
    }
}

