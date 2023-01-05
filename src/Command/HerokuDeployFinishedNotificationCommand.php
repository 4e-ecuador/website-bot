<?php

namespace App\Command;

use App\Exception\EmojiNotFoundException;
use App\Service\EmojiService;
use App\Service\TelegramBotHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

#[AsCommand(
    name: 'HerokuDeployFinishedNotification',
    description: 'Bot message on Heroku finished deploy'
)]
class HerokuDeployFinishedNotificationCommand extends Command
{

    public function __construct(
        private readonly TelegramBotHelper $telegramBotHelper,
        private readonly EmojiService $emojiService,
        #[Autowire('%env(PAGE_BASE_URL)%')] private readonly string $pageBaseUrl
    ) {
        parent::__construct();
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
            $this->pageBaseUrl
        );

        $this->telegramBotHelper->sendMessage($groupId, $message, true);

        $io->success('Message has been sent!');

        return Command::SUCCESS;
    }
}
