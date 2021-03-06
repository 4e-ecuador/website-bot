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
    public function __construct(private CsvParser $csvParser, private TelegramAdminMessageHelper $telegramAdminMessageHelper, private TelegramMessageHelper $telegramMessageHelper, private MedalChecker $medalChecker, private AgentStatRepository $agentStatRepository, private TranslatorInterface $translator)
    {
    }

    /**
     * @throws InvalidCsvException
     * @throws StatsAlreadyAddedException
     * @throws StatsNotAllException
     */
    public function createEntryFromCsv(Agent $agent, $csv): AgentStat
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

    public function getImportResult(AgentStat $statEntry): ImportResult
    {
        $importResult = new ImportResult();
        $previousEntry = $this->agentStatRepository->getPrevious($statEntry);

        if (!$previousEntry) {
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
        if (null === $agent) {
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
    }

    private function getMethodName(string $vName): string
    {
        return 'set'.implode('', array_map('ucfirst', explode('-', $vName)));
    }
}
