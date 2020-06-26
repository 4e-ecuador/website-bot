<?php

namespace App\Command\Test;

use App\Repository\AgentRepository;
use App\Repository\IngressEventRepository;
use App\Service\TelegramBotHelper;
use App\Type\CustomMessage\NotifyEventsMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;

class TestNotifyEventsMessageCommand extends Command
{
    protected static $defaultName = 'bot:test:NotifyEventsMessage';

    /**
     * @var TelegramBotHelper
     */
    private $telegramBotHelper;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var IngressEventRepository
     */
    private $ingressEventRepository;

    public function __construct(TelegramBotHelper $telegramBotHelper, TranslatorInterface $translator, IngressEventRepository $ingressEventRepository)
    {
        parent::__construct();

        $this->telegramBotHelper = $telegramBotHelper;
        $this->translator = $translator;

        $this->ingressEventRepository = $ingressEventRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $firstAnnounce = false;
        $message = (new NotifyEventsMessage($this->telegramBotHelper, $this->ingressEventRepository,  $this->translator, $firstAnnounce))->getText();

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

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return 0;
    }
}
