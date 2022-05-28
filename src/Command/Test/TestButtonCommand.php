<?php

namespace App\Command\Test;

use App\Service\TelegramBotHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

#[AsCommand(name: 'bot:test:button')]
class TestButtonCommand extends Command
{
    public function __construct(private readonly TelegramBotHelper $telegramBotHelper)
    {
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

        $this->telegramBotHelper->sendButtonMessage('test');

        $io->success('Message has been sent!');

        return Command::SUCCESS;
    }
}
