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

class StatsImporter
{
    private CsvParser $csvParser;
    private AgentStatRepository $agentStatRepository;
    private TranslatorInterface $translator;
    private TelegramBotHelper $telegramBotHelper;
    private MedalChecker $medalChecker;
    private ImportResult $importResult;

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

        $this->importResult = new ImportResult();
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

    public function checkImport(
        AgentStat $statEntry,
        Agent $agent,
        User $user
    ): ImportResult {
        $previousEntry = $this->agentStatRepository->getPrevious($statEntry);

        if (!$previousEntry) {
            // First import
            $this->importResult->currents = $this->medalChecker->checkLevels(
                $statEntry
            );

            return $this->importResult;
        }

        $this->importResult->diff = $statEntry->computeDiff($previousEntry);

        $this->sendAdminMessages($statEntry, $agent, $user);
        $this->sendGroupMessages($previousEntry, $statEntry, $agent, $user);

        return $this->importResult;
    }

    private function sendAdminMessages(
        AgentStat $statEntry,
        Agent $agent,
        User $user
    ): void {
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
    }

    private function sendGroupMessages(
        AgentStat $previousEntry,
        AgentStat $statEntry,
        Agent $agent,
        User $user
    ): void {
        if (in_array('ROLE_INTRO_AGENT', $user->getRoles(), true)) {
            $groupName = 'intro';
        } else {
            $groupName = 'default';
        }

        // Medal(s) gained
        $medalUps = $this->medalChecker->getUpgrades(
            $previousEntry,
            $statEntry
        );
        if ($medalUps) {
            $this->importResult->medalUps = $medalUps;
            $this->telegramBotHelper->sendNewMedalMessage(
                $groupName,
                $agent,
                $medalUps
            );
        }

        // Level changed
        $previousLevel = $previousEntry->getLevel();
        if ($previousLevel && $statEntry->getLevel() !== $previousLevel) {
            $this->importResult->newLevel = $statEntry->getLevel();
            $this->telegramBotHelper->sendLevelUpMessage(
                $groupName,
                $agent,
                $this->importResult->newLevel,
                $statEntry->getRecursions()?:0
            );
        }

        // Recursions
        $recursions = $statEntry->getRecursions();
        if ($recursions) {
            $previousRecursions = $previousEntry->getRecursions();
            if (!$previousRecursions || $recursions > $previousRecursions) {
                $this->importResult->recursions = $recursions;
                $this->telegramBotHelper->sendRecursionMessage(
                    $groupName,
                    $agent,
                    $recursions
                );
            }
        }
    }

    private function getMethodName(string $vName): string
    {
        return 'set'.implode('', array_map('ucfirst', explode('-', $vName)));
    }
}
