<?php

namespace App\BotCommand;

use App\Exception\EmojiNotFoundException;
use App\Service\CiteService;
use App\Service\EmojiService;
use BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Update;

class Cite extends AbstractCommand implements PublicCommandInterface
{
    public function __construct(
        private CiteService $citeService,
        private EmojiService $emojiService
    ) {
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
     * @throws EmojiNotFoundException
     */
    public function execute(BotApi $api, Update $update): void
    {
        $silhouette = $this->emojiService->getEmoji('silhouette')
            ->getBytecode();

        $api->sendMessage(
            $update->getMessage()->getChat()->getId(),
            $silhouette.' _'.$this->citeService->getRandomCite().'_',
            'markdown'
        );
    }
}
