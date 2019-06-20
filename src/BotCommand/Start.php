<?php


namespace App\BotCommand;

use App\Repository\AgentRepository;
use BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

class Start extends AbstractCommand implements PublicCommandInterface
{
    private $repository;

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return '/start';
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return 'Start command';
    }

    /**
     * @inheritDoc
     */
    public function execute(BotApi $api, Update $update)
    {
        $text = "I'm alive =;)";
        $api->sendMessage($update->getMessage()->getChat()->getId(), $text, 'markdown');

        // @todo add chat id to database
    }
}
