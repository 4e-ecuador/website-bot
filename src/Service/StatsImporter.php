<?php

namespace App\Service;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Entity\User;
use App\Exception\InvalidCsvException;
use App\Exception\StatsAlreadyAddedException;
use App\Exception\StatsNotAllException;
use App\Repository\AgentStatRepository;
use App\Type\ImportResult;
use DateTime;
use Exception;
use RuntimeException;
use Symfony\Contracts\Translation\TranslatorInterface;
use TelegramBot\Api\InvalidArgumentException;
use UnexpectedValueException;

class StatsImporter
{
    public function __construct(
        private readonly CsvParser $csvParser,
        private readonly TelegramAdminMessageHelper $telegramAdminMessageHelper,
        private readonly TelegramMessageHelper $telegramMessageHelper,
        private readonly MedalChecker $medalChecker,
        private readonly AgentStatRepository $agentStatRepository,
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * @throws InvalidCsvException
     * @throws StatsAlreadyAddedException
     * @throws StatsNotAllException
     */
    public function createEntryFromCsv(Agent $agent, string $csv): AgentStat
    {
        return $this->updateEntryFromCsv(new AgentStat(), $agent, $csv);
    }

    /**
     * @throws StatsNotAllException
     * @throws StatsAlreadyAddedException
     * @throws Exception
     * @throws InvalidCsvException
     */
    public function updateEntryFromCsv(
        AgentStat $statEntry,
        Agent $agent,
        string $csv
    ): AgentStat {
        $parsed = $this->csvParser->parse($csv);

        if (count($parsed) !== 1) {
            throw new RuntimeException('CSV is BAD: '.count($parsed));
        }

        foreach ($parsed as $date => $values) {
            $statEntry
                ->setDatetime(new DateTime($date))
                ->setAgent($agent);

            if ($this->agentStatRepository->has($statEntry)) {
                throw new StatsAlreadyAddedException(
                    $this->translator->trans('Stat entry already added!')
                );
            }

            foreach ($values as $vName => $value) {
                $methodName = $this->getMethodName($vName);
                if (method_exists($statEntry, $methodName)) {
                    $statEntry->$methodName($value);
                } else {
                    throw new UnexpectedValueException(
                        'Method not found: '.$methodName
                    );
                }
            }
        }

        return $statEntry;
    }

    public function getImportResult(
        AgentStat $statEntry,
        ?AgentStat $previousEntry = null
    ): ImportResult {
        $importResult = new ImportResult();
        $previousEntry = $previousEntry
            ?: $this->agentStatRepository->getPrevious($statEntry);

        if (!$previousEntry instanceof \App\Entity\AgentStat) {
            // First import
            $importResult->currents = $this->medalChecker
                ->checkLevels($statEntry);

            return $importResult;
        }

        $importResult->diff = $statEntry->computeDiff($previousEntry);

        $importResult->medalUps = $this->medalChecker
            ->getUpgrades($previousEntry, $statEntry);

        $importResult->medalDoubles = $this->medalChecker
            ->getDoubles($previousEntry, $statEntry);

        $previousLevel = $previousEntry->getLevel();
        if ($previousLevel && $statEntry->getLevel() !== $previousLevel) {
            $importResult->newLevel = $statEntry->getLevel();
        }

        $recursions = $statEntry->getRecursions();
        if ($recursions) {
            $previousRecursions = $previousEntry->getRecursions();
            if (!$previousRecursions || $recursions > $previousRecursions) {
                $importResult->recursions = $recursions;
            }
        }

        $monthsSubscribed = $statEntry->getMonthsSubscribed();
        if ($monthsSubscribed) {
            $previousMonthsSubscribed = $previousEntry->getMonthsSubscribed();
            if (!$previousMonthsSubscribed) {
                $importResult->coreSubscribed[] = 'core';
            }

            if ($monthsSubscribed >= 24
                && $monthsSubscribed !== $previousMonthsSubscribed
            ) {
                $importResult->coreSubscribed[] = 'dual_core';
            }

            if ($monthsSubscribed >= 36
                && $monthsSubscribed !== $previousMonthsSubscribed
            ) {
                $importResult->coreSubscribed[] = 'core_year3';
            }
        }

        return $importResult;
    }

    /**
     * @throws \TelegramBot\Api\Exception
     * @throws InvalidArgumentException
     */
    public function sendResultMessages(
        ImportResult $result,
        AgentStat $statEntry,
        User $user
    ): void {
        $agent = $user->getAgent();
        if (!$agent instanceof \App\Entity\Agent) {
            throw new UnexpectedValueException('Agent not found');
        }

        /*
         * Admin messages
         */
        if ($statEntry->getFaction() !== 'Enlightened') {
            // Smurf detected!!!
            $this->telegramAdminMessageHelper->sendSmurfAlertMessage(
                'admin',
                $user,
                $agent,
                $statEntry
            );
        }

        if ($agent->getNickname() !== $statEntry->getNickname()) {
            // Nickname mismatch
            $this->telegramAdminMessageHelper->sendNicknameMismatchMessage(
                'admin',
                $user,
                $agent,
                $statEntry
            );
        }

        /*
         * Group messages
         */
        $groupName = (in_array('ROLE_INTRO_AGENT', $user->getRoles(), true))
            ? 'intro'
            : 'default';

        // Medal(s) gained
        if ($result->medalUps) {
            $this->telegramMessageHelper->sendNewMedalMessage(
                $groupName,
                $agent,
                $result->medalUps
            );
        }

        // Medal doubles
        if ($result->medalDoubles) {
            $this->telegramMessageHelper->sendMedalDoubleMessage(
                $groupName,
                $agent,
                $result->medalDoubles
            );
        }

        // Level changed
        if ($result->newLevel) {
            $this->telegramMessageHelper->sendLevelUpMessage(
                $groupName,
                $agent,
                $result->newLevel,
                $statEntry->getRecursions() ?: 0
            );
        }

        // Recursions
        if ($result->recursions) {
            $this->telegramMessageHelper->sendRecursionMessage(
                $groupName,
                $agent,
                $result->recursions
            );
        }

        // CORE Subscription
        // if ($result->coreSubscribed) {
        //     $this->telegramMessageHelper->sendNewMedalMessage(
        //         $groupName,
        //         $agent,
        //         $result->medalUps
        //     );
        // }
    }

    private function getMethodName(string $vName): string
    {
        return 'set'.implode('', array_map('ucfirst', explode('-', $vName)));
    }
}
