<?php

namespace App\Command;

use App\Service\TelegramBotHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TravisFinishedNofiticationCommand extends Command
{
    protected static $defaultName = 'TravisFinishedNofitication';

    /**
     * @var TelegramBotHelper
     */
    private $telegramBotHelper;

    public function __construct(TelegramBotHelper $telegramBotHelper)
    {
        parent::__construct();

        $this->telegramBotHelper = $telegramBotHelper;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $groupId = $this->telegramBotHelper->getGroupId('admin');


        echo "Group: '$groupId'";
        var_dump($groupId);

        $dd = $groupId + 1;

var_dump($dd);

        $message = [];

        $result = getenv('TRAVIS_TEST_RESULT');

        if ($result) {
            $status = 'failed '.$this->telegramBotHelper->getEmoji('cross-mark');
        } else {
            $status = 'succeeded '.$this->telegramBotHelper->getEmoji('check-mark');
        }

        $message[] = "Travis build *$status!*";
        $message[] = '`Repository:  '.getenv('TRAVIS_REPO_SLUG').'`';
        $message[] = '`Branch:      '.getenv('TRAVIS_BRANCH').'`';
        $message[] = '*Commit Msg:*';
        $message[] = getenv('TRAVIS_COMMIT_MESSAGE');
        $message[] = '[Job Log here]('.getenv('TRAVIS_JOB_WEB_URL').')';

        echo implode("\n", $message);

        $this->telegramBotHelper->sendMessage($groupId, implode("\n", $message), false);

        $io->success('Message has been sent!');

        return 0;
    }
}
