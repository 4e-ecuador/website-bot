<?php

namespace App\Command\Test;

use App\Repository\AgentRepository;
use App\Service\TelegramBotHelper;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

class TestRecursionMessageCommand extends Command
{
    protected static $defaultName = 'bot:test:RecursionMessage';// Type must be defined in base class :(

    private AgentRepository $agentRepository;
    private TelegramBotHelper $telegramBotHelper;

    public function __construct(
        TelegramBotHelper $telegramBotHelper,
        AgentRepository $agentRepository
    ) {
        parent::__construct();
        $this->agentRepository = $agentRepository;
        $this->telegramBotHelper = $telegramBotHelper;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add a short description for your command');
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

        $this->telegramBotHelper
            ->sendRecursionMessage('test', $agent, $recursions);

        $io->success('Message sent!');

        return 0;
    }
}
