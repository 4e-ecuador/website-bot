<?php

namespace App\Command;

use App\Exception\EmojiNotFoundException;
use App\Service\EmojiService;
use App\Service\TelegramBotHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

#[AsCommand(
    name: 'app:TravisFinishedNotification',
    description: 'Send a notification on travis job finish - used in travis.yml'
)]
class TravisFinishedNotificationCommand extends Command
{
    public function __construct(
        private readonly TelegramBotHelper $telegramBotHelper,
        private readonly EmojiService $emojiService
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws EmojiNotFoundException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        $groupId = $this->telegramBotHelper->getGroupId('admin');

        $message = [];

        $result = getenv('TRAVIS_TEST_RESULT');

        if ($result) {
            $emoji = $this->emojiService->getEmoji('cross-mark')->getBytecode();
            $message[] = $emoji.' Travis build *failed!*';
        } else {
            $emoji = $this->emojiService->getEmoji('check-mark')->getBytecode();
            $message[] = $emoji.' Travis build *succeeded!*';
        }

        $message[] = '`Repository:  '.getenv('TRAVIS_REPO_SLUG').'`';
        $message[] = '`Branch:      '.getenv('TRAVIS_BRANCH').'`';
        $message[] = '*Commit Msg:*';
        $message[] = getenv('TRAVIS_COMMIT_MESSAGE');
        $message[] = '[Job Log here]('.getenv('TRAVIS_JOB_WEB_URL').')';

        echo implode("\n", $message);

        $this->telegramBotHelper->sendMessage(
            $groupId,
            implode("\n", $message),
            true
        );

        $io->success('Message has been sent!');

        return Command::SUCCESS;
    }
}
