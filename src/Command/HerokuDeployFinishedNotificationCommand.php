<?php

namespace App\Command;

use App\Service\TelegramBotHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

class HerokuDeployFinishedNotificationCommand extends Command
{
    protected static $defaultName = 'HerokuDeployFinishedNotification';// Type must be defined in base class :(

    private TelegramBotHelper $telegramBotHelper;
    private string $pageBase;

    public function __construct(
        TelegramBotHelper $telegramBotHelper,
        string $pageBaseUrl
    ) {
        parent::__construct();

        $this->telegramBotHelper = $telegramBotHelper;
        $this->pageBase = $pageBaseUrl;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument(
                'arg1',
                InputArgument::OPTIONAL,
                'Argument description'
            )
            ->addOption(
                'option1',
                null,
                InputOption::VALUE_NONE,
                'Option description'
            );
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

        $groupId = $this->telegramBotHelper->getGroupId('admin');
        $message = [];

        $message[] = sprintf('New release on %s', $this->pageBase);

        $this->telegramBotHelper->sendMessage(
            $groupId,
            implode("\n", $message),
            true
        );

        $io->success('Message has been sent!');

        return 0;
    }
}
