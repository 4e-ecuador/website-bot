<?php

namespace App\Command\Test;

use App\Service\TelegramBotHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

class TestButtonCommand extends Command
{
    protected static $defaultName = 'TestButton';// Type must be defined in base class :(

    private TelegramBotHelper $telegramBotHelper;

    public function __construct(TelegramBotHelper $telegramBotHelper)
    {
        $this->telegramBotHelper = $telegramBotHelper;

        parent::__construct();
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

        $this->telegramBotHelper->sendButtonMessage('test');

        $io->success(
            'You have a new command! Now make it your own! Pass --help to see your options.'
        );

        return 0;
    }
}
