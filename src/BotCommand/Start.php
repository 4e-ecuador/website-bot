<?php

namespace App\BotCommand;

use BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

class Start extends AbstractCommand implements PublicCommandInterface
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return '/start';
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return 'Start command';
    }

    /**
     * @inheritDoc
     */
    public function execute(BotApi $api, Update $update)
    {
        $id = '000';
        $userName = '';
        $message = $update->getMessage();
        if ($message) {
            $user = $message->getFrom();
            if ($user) {
                $id = $user->getId();
                $userName = $user->getUsername();
            }
        }
        $text = "I'm alive =;) - ".$id.$userName;//$update->getMessage()->getChat()->getId();
        $api->sendMessage(
            $update->getMessage()->getChat()->getId(),
            $text,
            'markdown'
        );
        // @todo add chat id to database
    }
}
