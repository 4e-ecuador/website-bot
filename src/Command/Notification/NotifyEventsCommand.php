<?php

namespace App\Command\Notification;

use App\Repository\AgentRepository;
use App\Service\TelegramBotHelper;
use App\Type\CustomMessage\NotifyEventsMessage;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class NotifyEventsCommand extends Command
{
    protected static $defaultName = 'bot:notify:events';

    public function __construct(
        private NotifyEventsMessage $notifyEventsMessage,
        private AgentRepository $agentRepository,
        private TelegramBotHelper $telegramBotHelper
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Send notifications of upcoming events.')
            ->addOption(
                'first-announce',
                null,
                InputOption::VALUE_NONE,
                'The first announcement'
            );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);
        $firstAnnounce = $input->getOption('first-announce');
        $message = $this->notifyEventsMessage
            ->setFirstAnnounce($firstAnnounce)
            ->getText();

        if (!$message) {
            // No message - no events :(
            return 0;
        }

        $agents = $this->agentRepository->findNotifyAgents();

        foreach ($agents as $agent) {
            if ($agent->getHasNotifyEvents()) {
                try {
                    $this->telegramBotHelper->sendMessage(
                        $agent->getTelegramId(),
                        $message
                    );
                } catch (Exception $exception) {
                    $io->warning(
                        $exception->getMessage().' - Agent: '
                        .$agent->getNickname()
                    );
                }
            }
        }

        $io->success(
            sprintf(
                'Notifications have been sent to %d agents!',
                count($agents)
            )
        );

        return 0;
    }
}
