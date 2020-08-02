<?php

namespace App\Command\Test;

use App\Repository\AgentRepository;
use App\Service\TelegramMessageHelper;
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
    private TelegramMessageHelper $telegramMessageHelper;

    public function __construct(
        TelegramMessageHelper $telegramMessageHelper,
        AgentRepository $agentRepository
    ) {
        parent::__construct();
        $this->agentRepository = $agentRepository;
        $this->telegramMessageHelper = $telegramMessageHelper;
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

        $this->telegramMessageHelper
            ->sendRecursionMessage('test', $agent, $recursions);

        $io->success('Message has been sent!');

        return Command::SUCCESS;
    }
}
