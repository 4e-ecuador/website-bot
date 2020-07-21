<?php

namespace App\Command\Notification;

use App\Repository\AgentRepository;
use App\Service\TelegramMessageHelper;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UploadStatsReminder extends Command
{
    protected static $defaultName = 'app:send:notification:uploadStatsReminder';// Type must be defined in base class :(

    private AgentRepository $agentRepository;
    private TelegramMessageHelper $telegramMessageHelper;

    public function __construct(
        TelegramMessageHelper $telegramMessageHelper,
        AgentRepository $agentRepository
    ) {
        parent::__construct();

        $this->agentRepository = $agentRepository;
        $this->telegramMessageHelper = $telegramMessageHelper;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add a short description for your command');
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        $agents = $this->agentRepository->findNotifyAgents();

        $count = 0;

        foreach ($agents as $agent) {
            if ($agent->getHasNotifyUploadStats()&&$agent->getTelegramId()) {
                try {
                    $this->telegramMessageHelper->sendNotifyUploadReminderMessage(
                        $agent->getTelegramId()
                    );
                    $count++;
                } catch (Exception $exception) {
                    $io->warning(
                        $exception->getMessage().' - '.$agent->getNickname()
                    );
                }
            }
        }

        $io->success(sprintf('Messages have been sent to %d agents.', $count));

        return 0;
    }
}
