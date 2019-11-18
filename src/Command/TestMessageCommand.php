<?php

namespace App\Command;

use App\Service\TelegramBotHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestMessageCommand extends Command
{
    protected static $defaultName = 'testMessage';
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
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        try {
            $groupId = $_ENV['ANNOUNCE_GROUP_ID_1'];

            $text = 'test '.date('Y-m-d H:i:s');
            $this->telegramBotHelper->sendTestMessage($text, $groupId);
            $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());
        }


        return 0;
    }
}
