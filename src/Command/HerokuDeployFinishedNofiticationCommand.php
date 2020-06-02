<?php

namespace App\Command;

use App\Repository\AgentStatRepository;
use App\Service\TelegramBotHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class HerokuDeployFinishedNofiticationCommand extends Command
{
    protected static $defaultName = 'HerokuDeployFinishedNofitication';
    /**
     * @var TelegramBotHelper
     */
    private $telegramBotHelper;

    /**
     * @var AgentStatRepository
     */
    private $agentStatRepository;

    public function __construct(TelegramBotHelper $telegramBotHelper, AgentStatRepository $agentStatRepository)
    {
        parent::__construct();
        $this->telegramBotHelper = $telegramBotHelper;
        $this->agentStatRepository = $agentStatRepository;
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
        // $arg1 = $input->getArgument('arg1');
        //
        // if ($arg1) {
        //     $io->note(sprintf('You passed an argument: %s', $arg1));
        // }
        //
        // if ($input->getOption('option1')) {
        //     // ...
        // }
        //
        // $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        $groupId = $this->telegramBotHelper->getGroupId('test');
        $message = [];

        $message[] = 'Status update: '.date('Y-m-d H:i:s');

        $message[] = '';

        foreach ($_ENV as $k => $v) {
            $message[] = $k.' => '.$v;

        }
        $message[] = '';

        $this->telegramBotHelper->sendMessage($groupId, implode("\n", $message));


        return 0;
    }
}
