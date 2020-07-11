<?php

namespace App\Command;

use App\Exception\EmojiNotFoundException;
use App\Service\EmojiService;
use App\Service\TelegramBotHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

class TravisFinishedNofiticationCommand extends Command
{
    protected static $defaultName = 'TravisFinishedNofitication';// Type must be defined in base class :(

    private TelegramBotHelper $telegramBotHelper;
    private EmojiService $emojiService;

    public function __construct(
        TelegramBotHelper $telegramBotHelper,
        EmojiService $emojiService
    ) {
        parent::__construct();

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
            $emoji = 'failed '.$this->emojiService
                    ->getEmoji('cross-mark')->getBytecode();
            $message[] = $emoji.' Travis build *failed!*';
        } else {
            $emoji = 'succeeded '.$this->emojiService
                    ->getEmoji('check-mark')->getBytecode();
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

        return 0;
    }
}
