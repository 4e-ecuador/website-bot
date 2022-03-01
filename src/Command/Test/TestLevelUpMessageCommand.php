<?php

namespace App\Command\Test;

use App\Entity\Agent;
use App\Service\TelegramMessageHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

class TestLevelUpMessageCommand extends Command
{
    protected static $defaultName = 'bot:test:levelUpMessage';

    public function __construct(
        private readonly TelegramMessageHelper $telegramMessageHelper
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Test LevelUp Message');
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $agent = (new Agent())
            ->setNickname('nikp3h');

        $level = 6;
        $recursions = 3;

        $this->telegramMessageHelper->sendLevelUpMessage(
            'test',
            $agent,
            $level,
            $recursions
        );

        return Command::SUCCESS;
    }
}
