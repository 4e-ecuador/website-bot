<?php

namespace App\Command\Notification;

use App\Repository\AgentRepository;
use App\Service\TelegramBotHelper;
use App\Type\CustomMessage\NotifyUploadReminder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;

class UploadStatsReminder extends Command
{
    protected static $defaultName = 'app:send:notification:uploadStatsReminder';

    /**
     * @var TelegramBotHelper
     */
    private $telegramBotHelper;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var AgentRepository
     */
    private $agentRepository;

    public function __construct(
        TelegramBotHelper $telegramBotHelper,
        TranslatorInterface $translator,
        AgentRepository $agentRepository
    ) {
        $this->telegramBotHelper = $telegramBotHelper;
        $this->translator = $translator;
        $this->agentRepository = $agentRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command');
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        $message = (new NotifyUploadReminder(
            $this->telegramBotHelper,
            $this->translator
        ))->getText();

        $agents = $this->agentRepository->findNotifyAgents();

        $count = 0;

        foreach ($agents as $agent) {
            if ($agent->getHasNotifyUploadStats()) {
                try {
                    $this->telegramBotHelper->sendMessage(
                        $agent->getTelegramId(),
                        $message
                    );
                    $count++;
                } catch (\Exception $exception) {
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
