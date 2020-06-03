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
     * @var string
     */
    private $pageBase;

    public function __construct(TelegramBotHelper $telegramBotHelper, string $pageBase)
    {
        parent::__construct();

        $this->telegramBotHelper = $telegramBotHelper;
        $this->pageBase = $pageBase;
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

        $groupId = $this->telegramBotHelper->getGroupId('admin');
        $message = [];

        $message[] = 'Status update: '.date('Y-m-d H:i:s');
        $message[] = '';
        $message[] = sprintf('New release on %s', $this->pageBase);

        $this->telegramBotHelper->sendMessage($groupId, implode("\n", $message), true);

        $io->success('Message has been sent!');

        return 0;
    }
}
