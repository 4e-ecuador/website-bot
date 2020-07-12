<?php

namespace App\Command\Test;

use App\Repository\IngressEventRepository;
use App\Service\EmojiService;
use App\Service\TelegramBotHelper;
use App\Type\CustomMessage\NotifyEventsMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

class TestNotifyEventsMessageCommand extends Command
{
    protected static $defaultName = 'bot:test:NotifyEventsMessage';// Type must be defined in base class :(

    private TelegramBotHelper $telegramBotHelper;
    private TranslatorInterface $translator;
    private IngressEventRepository $ingressEventRepository;
    private EmojiService $emojiService;

    public function __construct(
        TelegramBotHelper $telegramBotHelper,
        TranslatorInterface $translator,
        IngressEventRepository $ingressEventRepository,
    EmojiService $emojiService
    ) {
        parent::__construct();

        $this->telegramBotHelper = $telegramBotHelper;
        $this->translator = $translator;
        $this->ingressEventRepository = $ingressEventRepository;
        $this->emojiService = $emojiService;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Bot test');
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);
        $firstAnnounce = false;
        $message = (new NotifyEventsMessage(
            $this->telegramBotHelper,
            $this->ingressEventRepository,
            $this->emojiService,
            $this->translator,
            $firstAnnounce
        ))->getText();

        if (!$message) {
            $io->warning('No events :(');

            return 0;
        }

        $chatId = $this->telegramBotHelper->getGroupId('test');
        $this->telegramBotHelper->sendMessage($chatId, $message);

        $io->success(
            'Message sent!'
        );

        return 0;
    }
}
