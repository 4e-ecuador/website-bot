<?php

namespace App\Command\Test;

use App\Service\TelegramBotHelper;
use App\Service\TelegramMessageHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

class TestNotifyUploadReminderMessageCommand extends Command
{
    protected static $defaultName = 'bot:test:NotifyUploadReminderMessage';

    public function __construct(
        private readonly TelegramMessageHelper $telegramMessageHelper,
        private readonly TelegramBotHelper $telegramBotHelper,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Bot Test');
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        $chatId = $this->telegramBotHelper->getGroupId('test');

        $this->telegramMessageHelper->sendNotifyUploadReminderMessage($chatId);

        $io->success('Message has been sent!');

        return Command::SUCCESS;
    }
}
