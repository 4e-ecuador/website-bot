<?php

namespace App\Type;

use App\Entity\Agent;
use App\Entity\User;
use App\Service\TelegramBotHelper;

abstract class AbstractCustomMessage
{
    protected TelegramBotHelper $telegramBotHelper;

    public function __construct(TelegramBotHelper $telegramBotHelper, ...$args)
    {
        $this->telegramBotHelper = $telegramBotHelper;
    }

    abstract public function getMessage(): array;

    public function getText(): string
    {
        return implode("\n", $this->getMessage());
    }

    protected function getAgentTelegramName(Agent $agent): string
    {
        return str_replace(
            '_',
            '\\_',
            $agent->getTelegramName()
                ?: $agent->getNickname()
        );
    }

    protected function getAgentUserData(Agent $agent, User $user): array
    {
        $message = [];
        $message[] = 'Agent: '.$agent->getNickname();
        $message[] = 'ID: '.$agent->getId();
        $message[] = '';
        $message[] = 'User: '.$user->getUsername();
        $message[] = 'ID: '.$user->getId();
        $message[] = '';
        $message[] = 'Please verify!';

        return $message;
    }
}
