<?php

namespace App\Command\Notification;

use App\Repository\AgentRepository;
use App\Repository\IngressEventRepository;
use App\Service\TelegramBotHelper;
use App\Type\CustomMessage\NotifyEventsMessage;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotifyEventsCommand extends Command
{
    protected static $defaultName = 'bot:notify:events';// Type must be defined in base class :(

    private IngressEventRepository $ingressEventRepository;
    private TranslatorInterface $translator;
    private AgentRepository $agentRepository;
    private TelegramBotHelper $telegramBotHelper;

    public function __construct(
        IngressEventRepository $ingressEventRepository,
        AgentRepository $agentRepository,
        TelegramBotHelper $telegramBotHelper,
        TranslatorInterface $translator
    ) {
        $this->ingressEventRepository = $ingressEventRepository;
        $this->translator = $translator;
        $this->agentRepository = $agentRepository;
        $this->telegramBotHelper = $telegramBotHelper;

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

        $message = (new NotifyEventsMessage(
            $this->telegramBotHelper,
            $this->ingressEventRepository,
            $this->translator,
            $firstAnnounce
        ))
            ->getMessage();

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
                        implode("\n", $message)
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
