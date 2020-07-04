<?php

namespace App\Service;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Entity\User;
use App\Exception\StatsAlreadyAddedException;
use App\Exception\StatsNotAllException;
use App\Repository\AgentStatRepository;
use App\Type\ImportResult;
use DateTime;
use Exception;
use RuntimeException;
use Symfony\Contracts\Translation\TranslatorInterface;
use UnexpectedValueException;

class StatsImporter
{
    private CsvParser $csvParser;
    private AgentStatRepository $agentStatRepository;
    private TranslatorInterface $translator;
    private TelegramBotHelper $telegramBotHelper;
    private MedalChecker $medalChecker;

    public function __construct(
        CsvParser $csvParser,
        TelegramBotHelper $telegramBotHelper,
        MedalChecker $medalChecker,
        AgentStatRepository $agentStatRepository,
        TranslatorInterface $translator
    ) {
        $this->csvParser = $csvParser;
        $this->agentStatRepository = $agentStatRepository;
        $this->translator = $translator;
        $this->telegramBotHelper = $telegramBotHelper;
        $this->medalChecker = $medalChecker;
    }

    /**
     * @throws StatsNotAllException
     * @throws StatsAlreadyAddedException
     * @throws Exception
     */
    public function updateEntityFromCsv(
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
                    // $this->addFlash(
                    //     'warning',
                    //     'method not found: '.$methodName.' '.$vName
                    // );
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

    public function sendResultMessages(
        ImportResult $result,
        AgentStat $statEntry,
        User $user
    ): self {
        $agent = $user->getAgent();
        if (null === $agent) {
            throw new UnexpectedValueException('Agent not found');
        }

        /*
         * Admin messages
         */
        if ($statEntry->getFaction() !== 'Enlightened') {
            // Smurf detected!!!
            $this->telegramBotHelper->sendSmurfAlertMessage(
                'admin',
                $user,
                $agent,
                $statEntry
            );
        }

        if ($agent->getNickname() !== $statEntry->getNickname()) {
            // Nickname mismatch
            $this->telegramBotHelper->sendNicknameMismatchMessage(
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
            $this->telegramBotHelper->sendNewMedalMessage(
                $groupName,
                $agent,
                $result->medalUps
            );
        }

        // Level changed
        if ($result->newLevel) {
            $this->telegramBotHelper->sendLevelUpMessage(
                $groupName,
                $agent,
                $result->newLevel,
                $result->recursions ?: 0
            );
        }

        // Recursions
        if ($result->recursions) {
            $this->telegramBotHelper->sendRecursionMessage(
                $groupName,
                $agent,
                $result->recursions
            );
        }

        return $this;
    }

    private function getMethodName(string $vName): string
    {
        return 'set'.implode('', array_map('ucfirst', explode('-', $vName)));
    }
}
