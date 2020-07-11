<?php

namespace App\Command\Test;

use App\Repository\AgentRepository;
use App\Service\EmojiService;
use App\Service\TelegramBotHelper;
use App\Type\CustomMessage\RecursionMessage;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

class TestRecursionMessageCommand extends Command
{
    protected static $defaultName = 'bot:test:RecursionMessage';// Type must be defined in base class :(

    private AgentRepository $agentRepository;
    private TranslatorInterface $translator;
    private TelegramBotHelper $telegramBotHelper;
    private EmojiService $emojiService;
    private string $pageBaseUrl;

    public function __construct(
        TelegramBotHelper $telegramBotHelper,
        EmojiService $emojiService,
        TranslatorInterface $translator,
        AgentRepository $agentRepository
    ) {
        parent::__construct();
        $this->agentRepository = $agentRepository;
        $this->translator = $translator;
        $this->pageBaseUrl = 'http://agents.enl.ec';// $pageBaseUrl;
        $this->telegramBotHelper = $telegramBotHelper;
        $this->emojiService = $emojiService;
    }

    protected function configure(): void
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

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NonUniqueResultException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        $agent = $this->agentRepository->findOneByNickName('nikp3h');
        $recursions = 66;
        $message = (new RecursionMessage(
            $this->telegramBotHelper,
            $this->emojiService,
            $this->translator,
            $agent,
            $recursions,
            $this->pageBaseUrl
        ))->getText();

        $io->text($message);

        $chatId = $this->telegramBotHelper->getGroupId('test');

        $this->telegramBotHelper->sendMessage($chatId, $message);

        $io->success('Message sent!');

        return 0;
    }
}
