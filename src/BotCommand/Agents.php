<?php

namespace App\BotCommand;

use BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Update;

class Agents extends AbstractCommand implements PublicCommandInterface
{
    public function getName(): string
    {
        return '/agents';
    }

    public function getDescription(): string
    {
        return 'Agents lookup command';
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function execute(BotApi $api, Update $update): void
    {
        $api->sendMessage(
            $update->getMessage()->getChat()->getId(),
            'not active'
        );
    }
}
