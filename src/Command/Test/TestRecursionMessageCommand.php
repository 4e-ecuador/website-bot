<?php

namespace App\Command\Test;

use App\Repository\AgentRepository;
use App\Service\TelegramMessageHelper;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

#[AsCommand(name: 'bot:test:RecursionMessage')]
class TestRecursionMessageCommand extends Command
{
    public function __construct(
        private readonly TelegramMessageHelper $telegramMessageHelper,
        private readonly AgentRepository $agentRepository
    ) {
        parent::__construct();
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
