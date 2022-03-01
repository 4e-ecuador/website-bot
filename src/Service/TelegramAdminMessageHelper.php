<?php

namespace App\Service;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Entity\User;
use App\Type\CustomMessage\NewUserMessage;
use App\Type\CustomMessage\NicknameMismatchMessage;
use App\Type\CustomMessage\SmurfAlertMessage;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Message;

class TelegramAdminMessageHelper
{
    public function __construct(
        private readonly TelegramBotHelper $telegramBotHelper,
        private readonly string $announceAdminCc,
        private readonly NewUserMessage $newUserMessage,
        private readonly NicknameMismatchMessage $nicknameMismatchMessage,
        private readonly SmurfAlertMessage $smurfAlertMessage
    ) {
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function sendNewUserMessage(int $chatId, User $user): Message
    {
        $message = $this->newUserMessage
            ->setUser($user)
            ->getText();

        return $this->telegramBotHelper->sendMessage($chatId, $message);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function sendSmurfAlertMessage(
        string $groupName,
        User $user,
        Agent $agent,
        AgentStat $statEntry
    ): Message {
        $message = $this->smurfAlertMessage
            ->setUser($user)
            ->setAgent($agent)
            ->setAnnounceAdminCc($this->announceAdminCc)
            ->setStatEntry($statEntry)
            ->getText();

        return $this->telegramBotHelper->sendMessage(
            $this->telegramBotHelper->getGroupId($groupName),
            str_replace('_', '\\_', $message)
        );
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function sendNicknameMismatchMessage(
        string $groupName,
        User $user,
        Agent $agent,
        AgentStat $statEntry
    ): Message {
        $message = $this->nicknameMismatchMessage
            ->setUser($user)
            ->setAgent($agent)
            ->setStatEntry($statEntry)
            ->getText();

        return $this->telegramBotHelper->sendMessage(
            $this->telegramBotHelper->getGroupId($groupName),
            str_replace('_', '\\_', $message)
        );
    }
}
