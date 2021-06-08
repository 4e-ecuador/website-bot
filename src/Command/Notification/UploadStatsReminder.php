<?php

namespace App\Command\Notification;

use App\Repository\AgentRepository;
use App\Service\TelegramMessageHelper;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:send:notification:uploadStatsReminder',
    description: 'Send a upload stats reminder on telegram'
)]
class UploadStatsReminder extends Command
{

    public function __construct(
        private TelegramMessageHelper $telegramMessageHelper,
        private AgentRepository $agentRepository
    ) {
        parent::__construct();
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

        return Command::SUCCESS;
    }
}
