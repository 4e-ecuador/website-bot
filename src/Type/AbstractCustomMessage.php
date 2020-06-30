<?php

namespace App\Type;

use App\Entity\Agent;
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
}
