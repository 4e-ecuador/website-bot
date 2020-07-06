<?php

namespace App\Command\Test;

use App\Entity\Agent;
use App\Service\TelegramBotHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

class TestLevelUpMessageCommand extends Command
{
    protected static $defaultName = 'TestLevelUpMessage';// Type must be defined in base class :(

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
        $agent = new Agent();

        $level = 6;
        $recursions = 3;

        $this->telegramBotHelper->sendLevelUpMessage(
            'test',
            $agent,
            $level,
            $recursions
        );

        return 0;
    }
}
