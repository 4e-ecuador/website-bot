<?php

namespace App\Command\Test;

use App\Service\TelegramBotHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestMessageCommand extends Command
{
    protected static $defaultName = 'app:bot:testMessage';

    /**
     * @var TelegramBotHelper
     */
    private $telegramBotHelper;

    public function __construct(TelegramBotHelper $telegramBotHelper)
    {
        $this->telegramBotHelper = $telegramBotHelper;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Send a bot message')
            ->addOption('group', null, InputOption::VALUE_OPTIONAL, 'Group name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            if ($input->getOption('group')) {
                if ('test' === $input->getOption('group')) {
                    $groupId = $_ENV['ANNOUNCE_GROUP_ID_TEST'];
                } else {
                    throw new \UnexpectedValueException('Unknown group');
                }

                $io->writeln('group set to: '.$input->getOption('group'));
            } else {
                $groupId = $_ENV['ANNOUNCE_GROUP_ID_1'];
            }

            $text = '`test '.date('Y-m-d H:i:s').' '.date_default_timezone_get()
                .'`';
            $io->writeln($text);
            $this->telegramBotHelper->sendMessage($groupId, $text);
            $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());
        }

        return 0;
    }
}
