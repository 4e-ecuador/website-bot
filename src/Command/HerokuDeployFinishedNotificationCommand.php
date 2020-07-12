<?php

namespace App\Command;

use App\Exception\EmojiNotFoundException;
use App\Service\EmojiService;
use App\Service\TelegramBotHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

class HerokuDeployFinishedNotificationCommand extends Command
{
    protected static $defaultName = 'HerokuDeployFinishedNotification';// Type must be defined in base class :(

    private TelegramBotHelper $telegramBotHelper;
    private EmojiService $emojiService;
    private string $pageBase;

    public function __construct(
        TelegramBotHelper $telegramBotHelper,
        EmojiService $emojiService,
        string $pageBaseUrl
    ) {
        parent::__construct();

        $this->telegramBotHelper = $telegramBotHelper;
        $this->pageBase = $pageBaseUrl;
        $this->emojiService = $emojiService;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Bot message on Heroku finished deploy.');
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws EmojiNotFoundException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        $groupId = $this->telegramBotHelper->getGroupId('admin');

        $message = sprintf(
            '%s New release on %s',
            $this->emojiService->getEmoji('sparkles')->getBytecode(),
            $this->pageBase
        );

        $this->telegramBotHelper->sendMessage($groupId, $message, true);

        $io->success('Message has been sent!');

        return 0;
    }
}
