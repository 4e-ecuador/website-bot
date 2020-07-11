<?php

namespace App\Command\Test;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Entity\User;
use App\Service\TelegramBotHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

class TestSmurfAlertMessageCommand extends Command
{
    protected static $defaultName = 'TestSmurfAlertMessage';// Type must be defined in base class :(

    private TelegramBotHelper $telegramBotHelper;

    public function __construct(TelegramBotHelper $telegramBotHelper)
    {
        $this->telegramBotHelper = $telegramBotHelper;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Test the smurf alert');
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

        $this->telegramBotHelper->sendSmurfAlertMessage(
            'test',
            $user,
            $agent,
            $statEntry
        );

        return 0;
    }
}
