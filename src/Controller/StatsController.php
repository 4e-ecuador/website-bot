<?php

namespace App\Controller;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Entity\TestStat;
use App\Exception\StatsNotAllException;
use App\Repository\AgentStatRepository;
use App\Repository\UserRepository;
use App\Service\CsvParser;
use App\Service\IntlDateHelper;
use App\Service\MedalChecker;
use App\Service\TelegramBotHelper;
use App\Type\BoardEntry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/stats")
 */
class StatsController extends AbstractController
{
    /**
     * @Route("/my-stats", name="my_stats")
     * @IsGranted("ROLE_INTRO_AGENT")
     */
    public function myStats(Security $security, AgentStatRepository $statRepository, MedalChecker $medalChecker, TranslatorInterface $translator): Response
    {
        $user = $security->getUser();

        $agent = $user->getAgent();

        if (!$agent) {
            throw $this->createAccessDeniedException($translator->trans('user.not.verified.2'));
        }

        $medalGroups = [];
        $latest = $statRepository->getAgentLatest($agent);

        if ($latest) {
            $medals = $medalChecker->checkLevels($latest);
            arsort($medals);
            $medalGroups = $medals;// $this->getMedalGroups($medals);
        }

        $customMedals = json_decode($agent->getCustomMedals(), true);

        return $this->render(
            'stats/mystats.html.twig',
            [
                'agent'             => $agent,
                'medalGroups'       => $medalGroups,
                'agentCustomMedals' => $customMedals,
                'latest'            => $latest,
            ]
        );
    }

    /**
     * @Route("/agent/{id}", name="agent_stats")
     * @IsGranted("ROLE_AGENT")
     */
    public function AgentStats(Agent $agent, AgentStatRepository $statRepository, MedalChecker $medalChecker): Response
    {
        $medalGroups = [];
        $latest = $statRepository->getAgentLatest($agent);

        if ($latest) {
            $medals = $medalChecker->checkLevels($latest);
            arsort($medals);
            $medalGroups = $medals;
        }

        $customMedals = json_decode($agent->getCustomMedals(), true);

        return $this->render(
            'stats/mystats.html.twig',
            [
                'agent'             => $agent,
                'medalGroups'       => $medalGroups,
                'customMedals'      => $medalChecker->getCustomMedalGroups(),
                'agentCustomMedals' => $customMedals,
                'latest'            => $latest,
            ]
        );
    }

    /**
     * @Route("/agent/data/{id}", name="agent_stats_data")
     * @IsGranted("ROLE_INTRO_AGENT")
     */
    public function agentStatsJson(Agent $agent, AgentStatRepository $statRepository): JsonResponse
    {
        $data = new \stdClass();

        $data->ap = [];
        $data->hacker = [];

        $entries = $statRepository->getAgentStats($agent, 'ASC');

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
     * @Route("/leaderboard", name="stats_leaderboard")
     * @IsGranted("ROLE_AGENT")
     */
    public function leaderBoard(AgentStatRepository $statRepository, UserRepository $userRepository): Response
    {
        return $this->render(
            'stats/leaderboard.html.twig',
            [
                'board'    => $this->getBoardEntries($userRepository, $statRepository),
                'cssClass' => 'col-sm-3 ',
            ]
        );
    }

    private function getBoardEntries(UserRepository $userRepository, AgentStatRepository $statRepository, string $typeOnly = 'all')
    {
        $users = $userRepository->findAll();
        $boardEntries = [];

        foreach ($users as $user) {
            $agent = $user->getAgent();

            if (!$agent) {
                continue;
            }

            $agentEntry = $statRepository->getAgentLatest($agent);

            if (!$agentEntry) {
                continue;
            }

            foreach ($agentEntry->getProperties() as $property) {
                if (in_array(
                    $property, [
                        'current_challenge',
                        'level',
                        'faction',
                        'nickname',
                    ]
                )
                ) {
                    continue;
                }

                $methodName = 'get'.str_replace('_', '', $property);
                if ($agentEntry->$methodName()) {
                    $boardEntries[$property][] = new BoardEntry($agent, $agentEntry->$methodName());
                }
            }

            $boardEntries['Fields/Links'][] = new BoardEntry(
                $agent,
                $agentEntry->getMindController() / $agentEntry->getConnector()
            );
        }

        foreach ($boardEntries as $type => $entries) {
            usort(
                $boardEntries[$type],
                static function ($a, $b) {
                    if ($a->getValue() === $b->getValue()) {
                        return 0;
                    }

                    return ($a->getValue() > $b->getValue()) ? -1 : 1;
                }
            );
        }

        if ($typeOnly && $typeOnly !== 'all') {
            if (array_key_exists($typeOnly, $boardEntries)) {
                return $boardEntries[$typeOnly];
            }

            throw new \UnexpectedValueException('Unknown type'.$typeOnly);
        }

        return $boardEntries;
    }

    /**
     * @Route("/leaderboard-detail", name="stats_leaderboard_detail")
     * @IsGranted("ROLE_AGENT")
     */
    public function leaderBoardDetail(AgentStatRepository $statRepository, UserRepository $userRepository, Request $request): Response
    {
        $item = $request->request->get('item', 'ap');

        $entries = $this->getBoardEntries($userRepository, $statRepository, $item);

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
     * @Route("/by-date", name="stats_by_date")
     * @IsGranted("ROLE_AGENT")
     */
    public function byDate(Request $request, AgentStatRepository $statRepository, MedalChecker $medalChecker, IntlDateHelper $dateHelper): Response
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $stats = [];
        $medalsGained = [];
        $medalsGained1 = [];

        if ($startDate && $endDate) {
            $entries = $statRepository->findByDate(
                new \DateTime($startDate), new \DateTime(
                    $endDate.' 23:59:59'
                )
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
                $a, static function ($a, $b) {
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
                'startDate'     => new \DateTime($startDate),
                'endDate'       => new \DateTime($endDate),
                'stats'         => $stats,
                'medalsGained'  => $medalsGained,
                'medalsGained1' => $medalsGained1,
            ]
        );
    }

    private function getMedalGroups($medals, int $medalsPerRow = 6): array
    {
        $medalGroups = [];
        $rowCount = 1;
        $groupCount = 0;

        foreach ($medals as $medalName => $level) {
            $medalGroups[$groupCount][$medalName] = $level;
            $rowCount++;

            if ($rowCount > $medalsPerRow) {
                $groupCount++;
                $rowCount = 1;
            }
        }

        return $medalGroups;
    }

    /**
     * @Route("/stat-import", name="stat_import", methods={"POST", "GET"})
     * @IsGranted("ROLE_INTRO_AGENT")
     */
    public function StatImport(
        Request $request, CsvParser $csvParser, MedalChecker $medalChecker,
        AgentStatRepository $agentStatRepository, Security $security, TelegramBotHelper $telegramBotHelper, TranslatorInterface $translator
    ): Response {
        $csv = $request->get('csv');
        $importType = $request->get('type');
        $medalUps = [];
        $currents = [];
        $currentEntry = null;
        $newLevel = null;
        $diff = [];

        $user = $security->getUser();

        $agent = $user->getAgent();

        if (!$agent) {
            throw $this->createAccessDeniedException($translator->trans('user.not.verified.2'));
        }

        if ($csv) {
            try {
                $parsed = $csvParser->parse($csv, $importType);

                foreach ($parsed as $date => $values) {
                    $statEntry = new AgentStat();

                    $statEntry->setDatetime(new \DateTime($date))
                        ->setAgent($agent);

                    if ($agentStatRepository->has($statEntry)) {
                        $this->addFlash('warning', $translator->trans('Stat entry already added!'));
                    } else {
                        foreach ($values as $vName => $value) {
                            $methodName = $medalChecker->getMethodName($vName);
                            if (method_exists($statEntry, $methodName)) {
                                $statEntry->$methodName($value);
                            } else {
                                $this->addFlash(
                                    'warning',
                                    'method not found: '.$methodName.' '.$vName
                                );
                            }
                        }

                        $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->persist($statEntry);
                        $entityManager->flush();

                        $currentEntry = $statEntry;
                    }
                }
            } catch (StatsNotAllException $exception) {
                $this->addFlash('danger', $exception->getMessage());
            } catch (\UnexpectedValueException $exception) {
                $this->addFlash('danger', $exception->getMessage());
            }
        }

        if ($currentEntry) {
            // Faction check
            if ($currentEntry->getFaction() !== 'Enlightened') {
                // Smurf detected!!!
                $telegramBotHelper->sendSmurfAlertMessage($user, $agent, $currentEntry);
            }

            if ($agent->getNickname() !== $currentEntry->getNickname()) {
                // Nickname mismatch
                $telegramBotHelper->sendNicknameMismatchMessage($user, $agent, $currentEntry);
            }

            $previousEntry = $agentStatRepository->getPrevious($currentEntry);

            if (!$previousEntry) {
                // First import
                $currents = $medalChecker->checkLevels($currentEntry);
            } else {
                $medalUps = $medalChecker->getUpgrades($previousEntry, $currentEntry);
                $diff = $currentEntry->getDiff($previousEntry);
                if (in_array('ROLE_INTRO_AGENT', $user->getRoles())) {
                    $groupId = $_ENV['ANNOUNCE_GROUP_ID_INTRO'];
                } else {
                    $groupId = $_ENV['ANNOUNCE_GROUP_ID_1'];
                }

                // Medal(s) gained - send a bot message !
                if ($medalUps && $groupId) {
                    $telegramBotHelper->sendNewMedalMessage($agent, $medalUps, $groupId);
                }

                // Level changed
                $previousLevel = $previousEntry->getLevel();
                if ($previousLevel
                    && $currentEntry->getLevel() !== $previousLevel
                ) {
                    $newLevel = $currentEntry->getLevel();
                    if ($groupId) {
                        $telegramBotHelper->sendLevelUpMessage($agent, $newLevel, $groupId);
                    }
                }
            }
        }

        return $this->render(
            'import/agent_stats.html.twig',
            [
                'ups'      => $medalUps,
                'diff'     => $diff,
                'currents' => $currents,
                'newLevel' => $newLevel,
            ]
        );
    }
}

