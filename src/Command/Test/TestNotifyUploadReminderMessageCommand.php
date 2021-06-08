<?php

namespace App\Command\Test;

use App\Repository\AgentRepository;
use App\Service\EmojiService;
use App\Service\TelegramBotHelper;
use App\Type\CustomMessage\NotifyUploadReminder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

class TestNotifyUploadReminderMessageCommand extends Command
{
    protected static $defaultName = 'bot:test:NotifyUploadReminderMessage';

    public function __construct(
        private TelegramBotHelper $telegramBotHelper,
        private TranslatorInterface $translator,
        private AgentRepository $agentRepository,
        private EmojiService $emojiService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Bot Test');
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);
        $message = (new NotifyUploadReminder(
            $this->telegramBotHelper,
            $this->translator,
            $this->emojiService
        ))->getText();

        if (false) {
            $agents = $this->agentRepository->findNotifyAgents();

            var_dump(count($agents));

            return Command::FAILURE;

            foreach ($agents as $agent) {
                if ($agent->getHasNotifyUploadStats()) {
                    try {
                        $this->telegramBotHelper->sendMessage(
                            $agent->getTelegramId(),
                            $message
                        );
                    } catch (Exception $exception) {
                        $io->warning(
                            $exception->getMessage().' - '.$agent->getNickname()
                        );
                    }
                }
            }
        }

        $chatId = $this->telegramBotHelper->getGroupId('test');
        $this->telegramBotHelper->sendMessage($chatId, $message);

        $io->text($message);

        $io->success('Message has been sent!');

        return Command::SUCCESS;
    }
}
