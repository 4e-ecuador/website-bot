<?php

namespace App\Command\Test;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Entity\User;
use App\Service\TelegramAdminMessageHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

#[AsCommand(name: 'bot:test:SmurfAlertMessage')]
class TestSmurfAlertMessageCommand extends Command
{
    public function __construct(
        private readonly TelegramAdminMessageHelper $telegramAdminMessageHelper
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $user = (new User())
            ->setEmail('test@example.com');

        $agent = (new Agent())
            ->setNickname('TEST');

        $statEntry = new AgentStat();

        $statEntry->setFaction('TEST');

        $this->telegramAdminMessageHelper->sendSmurfAlertMessage(
            'test',
            $user,
            $agent,
            $statEntry
        );

        return Command::SUCCESS;
    }
}
