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

class SendStatusCommand extends Command
{
    protected static $defaultName = 'sendStatus';
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
        $this->telegramBotHelper = $telegramBotHelper;
        $this->agentStatRepository = $agentStatRepository;

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

            $now = new \DateTime();

            $statsCount = $this->agentStatRepository->findTodays($now);

            $message = [];

            $message[] = 'Status update: '.date('Y-m-d H:i:s');
            $message[] = '';
            $message[] = sprintf('Stats uploaded: %d', count($statsCount));

            $this->telegramBotHelper->sendTestMessage($groupId, implode("\n", $message));

            $io->success('Finished!');
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());
            return 1;
        }

        return 0;
    }
}
