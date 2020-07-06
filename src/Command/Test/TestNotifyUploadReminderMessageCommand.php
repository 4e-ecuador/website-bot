<?php

namespace App\Command\Test;

use App\Repository\AgentRepository;
use App\Service\TelegramBotHelper;
use App\Type\CustomMessage\NotifyUploadReminder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;

class TestNotifyUploadReminderMessageCommand extends Command
{
    protected static $defaultName = 'TestNotifyUploadReminderMessage';

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
            ->setDescription('Add a short description for your command')
            ->addArgument(
                'arg1',
                InputArgument::OPTIONAL,
                'Argument description'
            )
            ->addOption(
                'option1',
                null,
                InputOption::VALUE_NONE,
                'Option description'
            );
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

        foreach ($agents as $agent) {
            if ($agent->getHasNotifyUploadStats()) {
                try {
                    $this->telegramBotHelper->sendMessage(
                        $agent->getTelegramId(),
                        $message
                    );
                } catch (\Exception $exception) {
                    $io->warning(
                        $exception->getMessage().' - '.$agent->getNickname()
                    );
                }
            }
        }

        $chatId = $this->telegramBotHelper->getGroupId('test');
        $io->text($chatId);
        $this->telegramBotHelper->sendMessage($chatId, $message);

        $io->text($message);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $io->success(
            'You have a new command! Now make it your own! Pass --help to see your options.'
        );

        return 0;
    }
}
