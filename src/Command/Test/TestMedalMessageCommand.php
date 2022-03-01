<?php

namespace App\Command\Test;

use App\Repository\AgentRepository;
use App\Service\TelegramMessageHelper;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestMedalMessageCommand extends Command
{
    protected static $defaultName = 'bot:test:medalMessage';

    public function __construct(
        private readonly TelegramMessageHelper $telegramMessageHelper,
        private readonly AgentRepository $agentRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Send a bot message')
            ->addOption(
                'group',
                null,
                InputOption::VALUE_OPTIONAL,
                'Group name'
            );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        try {
            if ($input->getOption('group')) {
                $groupName = $input->getOption('group');

                $io->writeln('group set to: '.$input->getOption('group'));
            } else {
                $groupName = 'test';
            }

            $agent = $this->agentRepository->findOneByNickName('nikp3h');

            $medalUps = ['purifier' => 5];

            $this->telegramMessageHelper->sendNewMedalMessage(
                $groupName,
                $agent,
                $medalUps
            );

            $io->success('Finished!');
        } catch (Exception $exception) {
            $io->error($exception->getMessage());
        }

        return Command::SUCCESS;
    }
}
