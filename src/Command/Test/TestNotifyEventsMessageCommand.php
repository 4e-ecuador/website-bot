<?php

namespace App\Command\Test;

use App\Service\TelegramBotHelper;
use App\Service\TelegramMessageHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

#[AsCommand(name: 'bot:test:NotifyEventsMessage')]
class TestNotifyEventsMessageCommand extends Command
{
    public function __construct(
        private readonly TelegramBotHelper $telegramBotHelper,
        private readonly TelegramMessageHelper $telegramMessageHelper
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);
        $firstAnnounce = false;

        $chatId = $this->telegramBotHelper->getGroupId('test');

        $this->telegramMessageHelper->sendNotifyEventsMessage(
            $chatId,
            $firstAnnounce
        );

        $io->success('Message has been sent!');

        return Command::SUCCESS;
    }
}
