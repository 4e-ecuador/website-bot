<?php
namespace App\Command\Test;

use App\Repository\AgentRepository;
use App\Service\TelegramBotHelper;
use App\Type\CustomMessage\RecursionMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;

class TestRecursionMessageCommand extends Command
{
    protected static $defaultName = 'bot:test:RecursionMessage';

    /**
     * @var AgentRepository
     */
    private $agentRepository;
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var string
     */
    private $pageBaseUrl;
    /**
     * @var TelegramBotHelper
     */
    private $telegramBotHelper;

    public function __construct(
        TelegramBotHelper $telegramBotHelper,
        TranslatorInterface $translator,
        AgentRepository $agentRepository
    ) {
        parent::__construct();
        $this->agentRepository = $agentRepository;
        $this->translator = $translator;
        $this->pageBaseUrl = 'http://agents.enl.ec';// $pageBaseUrl;
        $this->telegramBotHelper = $telegramBotHelper;
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
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $agent = $this->agentRepository->findOneByNickName('nikp3h');
        $recursions = 1;
        $message = (new RecursionMessage(
            $this->telegramBotHelper,
            $this->translator,
            $agent,
            $recursions,
            $this->pageBaseUrl
        ))->getText();

        $io->text($message);

        $chatId = $this->telegramBotHelper->getGroupId('test');
        // $io->text($chatId);
        $this->telegramBotHelper->sendMessage($chatId, $message);

        $io->success(
            'You have a new command! Now make it your own! Pass --help to see your options.'
        );

        return 0;
    }
}
