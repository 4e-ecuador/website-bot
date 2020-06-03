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
        $message = [];
        var_dump($_ENV);

        $result = $_ENV['TRAVIS_TEST_RESULT'];

        if ($result) {
            $status = 'failed '.$this->telegramBotHelper->getEmoji('cross-mark');
        } else {
            $status = 'succeeded '.$this->telegramBotHelper->getEmoji('check-mark');
        }

        $message[] = "Travis build *$status!*";
        $message[] = '`Repository:  '.$_ENV['TRAVIS_REPO_SLUG'].'`';
        $message[] = '`Branch:      '.$_ENV['TRAVIS_BRANCH'].'`';
        $message[] = '*Commit Msg:*';
        $message[] = $_ENV['TRAVIS_COMMIT_MESSAGE'];
        $message[] = '[Job Log here]('.$_ENV['TRAVIS_JOB_WEB_URL'].')';

        $this->telegramBotHelper->sendMessage($groupId, implode("\n", $message), false);

        $io->success('Message has been sent!');

        return 0;
    }
}
