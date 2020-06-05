<?php

namespace App\Type;

use App\Service\TelegramBotHelper;

abstract class AbstractCustomMessage
{
    /**
     * @var TelegramBotHelper
     */
    protected $telegramBotHelper;

    public function __construct(TelegramBotHelper $telegramBotHelper, ...$args)
    {
        $this->telegramBotHelper = $telegramBotHelper;
    }

    abstract public function getMessage(): array;

    public function getText(): string
    {
        return implode("\n", $this->getMessage());
    }
}
