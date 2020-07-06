<?php

namespace App\BotCommand;

use BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

class Guides extends AbstractCommand implements PublicCommandInterface
{
    public function getName(): string
    {
        return '/guias';
    }

    public function getDescription(): string
    {
        return 'Guides command';
    }

    public function execute(BotApi $api, Update $update): void
    {
        $text = "Guias blabla\n\n1. lala [aaa](https://aa.bb)\n1. lulu\n\nyau";

        $api->sendMessage(
            $update->getMessage()->getChat()->getId(),
            $text,
            'markdown'
        );
    }
}
