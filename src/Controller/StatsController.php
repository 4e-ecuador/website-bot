<?php

namespace App\Controller;

use App\Entity\Agent;
use App\Entity\User;
use App\Exception\StatsAlreadyAddedException;
use App\Exception\StatsNotAllException;
use App\Repository\AgentStatRepository;
use App\Repository\UserRepository;
use App\Service\LeaderBoardService;
use App\Service\MedalChecker;
use App\Service\StatsImporter;
use DateInterval;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use JsonException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use UnexpectedValueException;

#[Route(path: '/stats')]
class StatsController extends AbstractController
{
    /**
     * @IsGranted("ROLE_AGENT")
     * @throws NonUniqueResultException
     */
    #[Route(path: '/agent/{id}', name: 'agent_stats')]
    public function agentStats(Agent $agent, AgentStatRepository $statRepository, UserRepository $userRepository, MedalChecker $medalChecker): Response
    {
        $medalGroups = [];
        $latest = $statRepository->getAgentLatest($agent);
        if ($latest) {
            $medals = $medalChecker->checkLevels($latest);
            arsort($medals);
            $medalGroups = $medals;
        }
        $dateEnd = new DateTime();
        $dateStart = (new DateTime())->sub(new DateInterval('P30D'));
        try {
            $customMedals = json_decode(
                $agent->getCustomMedals(),
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
                'user'        => $userRepository->findByAgent($agent),
                'medalGroups' => $medalGroups,
                'latest'      => $latest,
                'dateStart'   => $dateStart,
                'dateEnd'     => $dateEnd,
                'first'       => $statRepository->getAgentLatest($agent, true),

                'agentCustomMedals' => $customMedals,
            ]
        );
    }
    /**
     * @IsGranted("ROLE_INTRO_AGENT")
     * @throws Exception
     */
    #[Route(path: '/agent/data/{id}/{startDate}/{endDate}', name: 'agent_stats_data')]
    public function agentStatsJson(Agent $agent, string $startDate, string $endDate, AgentStatRepository $statRepository): JsonResponse
    {
        $data = new stdClass();
        $data->ap = [];
        $data->hacker = [];
        $entries = $statRepository->findByDateAndAgent(
            new DateTime($startDate),
            new DateTime($endDate),
            $agent
        );
        $latest = null;
        if ($entries) {
            foreach ($entries as $entry) {
                // Get the correct datetime format for highcharts
                // See: https://stackoverflow.com/a/29234143/1906767
                $date = $entry->getDatetime()->format('U') * 1000;
                $data->ap[] = [$date, $entry->getAp()];
                $data->hacker[] = [$date, $entry->getHacker()];
            }
        }
        return new JsonResponse($data);
    }
    /**
     * @IsGranted("ROLE_AGENT")
     */
    #[Route(path: '/leaderboard', name: 'stats_leaderboard')]
    public function leaderBoard(UserRepository $userRepository, LeaderBoardService $leaderBoardService): Response
    {
        return $this->render(
            'stats/leaderboard.html.twig',
            [
                'board'    => $this->getBoardEntries(
                    $userRepository,
                    $leaderBoardService,
                ),
                'cssClass' => 'col-sm-3 ',
            ]
        );
    }
    /**
     * @IsGranted("ROLE_AGENT")
     */
    #[Route(path: '/leaderboard-detail', name: 'stats_leaderboard_detail')]
    public function leaderBoardDetail(AgentStatRepository $statRepository, UserRepository $userRepository, LeaderBoardService $leaderBoardService, Request $request): Response
    {
        $item = $request->request->get('item', 'ap');
        $entries = $this->getBoardEntries(
            $userRepository,
            $leaderBoardService,
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
    private function getBoardEntries(
        UserRepository $userRepository,
        LeaderBoardService $leaderBoardService,
        string $typeOnly = 'all'
    ) {
        $users = $userRepository->findAll();

        return $leaderBoardService->getBoard($users, $typeOnly);
    }
    /**
     * @IsGranted("ROLE_AGENT")
     * @throws Exception
     */
    #[Route(path: '/by-date', name: 'stats_by_date')]
    public function byDate(Request $request, AgentStatRepository $statRepository, MedalChecker $medalChecker): Response
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $stats = [];
        $medalsGained = [];
        $medalsGained1 = [];
        if ($startDate && $endDate) {
            $entries = $statRepository->findByDate(
                new DateTime($startDate),
                new DateTime($endDate.' 23:59:59')
            );
            $previous = [];

            foreach ($entries as $entry) {
                $agentName = $entry->getAgent()->getNickname();

                if (false === isset($previous[$agentName])) {
                    $previousEntry = $statRepository->getPrevious($entry);

                    $previous[$agentName] = $previousEntry
                        ? $medalChecker->checkLevels(
                            $previousEntry
                        ) : $medalChecker->checkLevels($entry);
                }

                $levels = $medalChecker->checkLevels($entry);
                $dateString = $entry->getDatetime()->format('Y-m-d');

                foreach ($levels as $name => $level) {
                    if (!$level) {
                        continue;
                    }
                    if (false === isset($previous[$agentName][$name])) {
                        $medalsGained[$dateString][$agentName][$name] = $level;
                        $medalsGained1[$name][] = [
                            'agent' => $agentName,
                            'level' => $level,
                        ];
                        $previous[$name] = $level;
                    } elseif ($previous[$agentName][$name] < $level) {
                        $medalsGained[$dateString][$agentName][$name] = $level;
                        $medalsGained1[$name][] = [
                            'agent' => $agentName,
                            'level' => $level,
                        ];
                        $previous[$agentName][$name] = $level;
                    }
                }
            }
        }
        foreach ($medalsGained1 as $name => $items) {
            $a = $items;
            usort(
                $a,
                static function ($a, $b) {
                    if ($a['level'] === $b['level']) {
                        return 0;
                    }

                    return ($a['level'] > $b['level']) ? -1 : 1;
                }
            );
            $medalsGained1[$name] = $a;
        }
        return $this->render(
            'stats/by_date.html.twig',
            [
                'startDate'     => new DateTime($startDate),
                'endDate'       => new DateTime($endDate),
                'stats'         => $stats,
                'medalsGained'  => $medalsGained,
                'medalsGained1' => $medalsGained1,
            ]
        );
    }
    /**
     * @IsGranted("ROLE_INTRO_AGENT")
     * @throws Exception
     */
    #[Route(path: '/stat-import', name: 'stat_import', methods: ['POST', 'GET'])]
    public function statImport(Request $request, Security $security, TranslatorInterface $translator, StatsImporter $statsImporter, string $appEnv): Response
    {
        /* @type User $user */
        $user = $security->getUser();
        if (!$user) {
            throw new UnexpectedValueException('User not found');
        }
        $agent = $user->getAgent();
        if (!$agent) {
            throw $this->createAccessDeniedException(
                $translator->trans('user.not.verified.2')
            );
        }
        $csv = $request->get('csv');
        if ($csv) {
            try {
                $entityManager = $this->getDoctrine()->getManager();

                $statEntry = $statsImporter
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
                    $translator->trans('Stats upload successful!')
                );

                $result = $statsImporter->getImportResult($statEntry);

                try {
                    $statsImporter
                        ->sendResultMessages($result, $statEntry, $user);
                } catch (\Exception $exception) {
                    $this->addFlash('warning', $translator->trans('Sorry but the message has not been sent :('));
                    if ('dev' === $appEnv) {
                        throw $exception;
                    }
                }

                // @TODO temporal FireBase token store
                $fireBaseToken = $request->get('fire_base_token');
                if ($fireBaseToken && !$user->getFireBaseToken()) {
                    $user->setFireBaseToken($fireBaseToken);
                    $entityManager->persist($user);
                    $entityManager->flush();
                }

                return $this->render(
                    'import/result.html.twig',
                    [
                        'statEntry'  => $statEntry,
                        'diff'       => $result->diff,
                        'medalUps'   => $result->medalUps,
                        'newLevel'   => $result->newLevel,
                        'recursions' => $result->recursions,
                        'currents'   => $result->currents,
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

