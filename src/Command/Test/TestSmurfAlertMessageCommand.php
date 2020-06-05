<?php

namespace App\Command\Test;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Entity\User;
use App\Service\TelegramBotHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestSmurfAlertMessageCommand extends Command
{
    protected static $defaultName = 'TestSmurfAlertMessage';
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
        $user = new User();

        $agent = new Agent();

        $statEntry = new AgentStat();

        $statEntry->setFaction('TEST');

        $this->telegramBotHelper->sendSmurfAlertMessage('test', $user, $agent, $statEntry);

        return 0;
    }
}
