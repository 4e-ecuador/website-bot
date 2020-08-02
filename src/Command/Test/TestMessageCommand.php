<?php

namespace App\Command\Test;

use App\Service\TelegramBotHelper;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use UnexpectedValueException;

class TestMessageCommand extends Command
{
    protected static $defaultName = 'bot:test:message';// Type must be defined in base class :(

    private TelegramBotHelper $telegramBotHelper;

    public function __construct(TelegramBotHelper $telegramBotHelper)
    {
        $this->telegramBotHelper = $telegramBotHelper;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Send a bot message')
            ->addOption(
                'group',
                null,
                InputOption::VALUE_OPTIONAL,
                'Group name'
            );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        try {
            if ($input->getOption('group')) {
                if ('test' === $input->getOption('group')) {
                    $groupId = $this->telegramBotHelper->getGroupId('test');
                } else {
                    throw new UnexpectedValueException('Unknown group');
                }

                $io->writeln('group set to: '.$input->getOption('group'));
            } else {
                $groupId = $this->telegramBotHelper->getGroupId();
            }

            $text = '`test '.date('Y-m-d H:i:s').' '.date_default_timezone_get()
                .'`';
            $io->writeln($text);
            $this->telegramBotHelper->sendMessage($groupId, $text);
            $io->success('Message has been sent!');
        } catch (Exception $exception) {
            $io->error($exception->getMessage());
        }

        return Command::SUCCESS;
    }
}
