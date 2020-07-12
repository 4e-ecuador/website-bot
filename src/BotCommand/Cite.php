<?php

namespace App\BotCommand;

use App\Service\CiteService;
use BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Update;

class Cite extends AbstractCommand implements PublicCommandInterface
{
    private CiteService $citeService;

    public function __construct(CiteService $citeService)
    {
        $this->citeService = $citeService;
    }

    public function getName(): string
    {
        return '/cite';
    }

    public function getDescription(): string
    {
        return 'Cites command';
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function execute(BotApi $api, Update $update): void
    {
        $api->sendMessage(
            $update->getMessage()->getChat()->getId(),
            '_'.$this->citeService->getRandomCite().'_',
            'markdown'
        );
    }
}
