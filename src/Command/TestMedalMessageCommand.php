<?php

namespace App\Command;

use App\Repository\AgentRepository;
use App\Service\TelegramBotHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestMedalMessageCommand extends Command
{
    protected static $defaultName = 'app:bot:testMedalMessage';

    /**
     * @var TelegramBotHelper
     */
    private $telegramBotHelper;
    /**
     * @var AgentRepository
     */
    private $agentRepository;

    public function __construct(TelegramBotHelper $telegramBotHelper, AgentRepository $agentRepository)
    {
        $this->telegramBotHelper = $telegramBotHelper;

        parent::__construct();
        $this->agentRepository = $agentRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Send a bot message')
            ->addOption('group', null, InputOption::VALUE_OPTIONAL, 'Group name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            if ($input->getOption('group')) {
                if ('test' === $input->getOption('group')) {
                    $groupId = $_ENV['ANNOUNCE_GROUP_ID_TEST'];
                } else {
                    throw new \UnexpectedValueException('Unknown group');
                }

                $io->writeln('group set to: '.$input->getOption('group'));
            } else {
                $groupId = $_ENV['ANNOUNCE_GROUP_ID_1'];
            }

            $agent = $this->agentRepository->findOneByNickName('nikp3h');

            $medalUps = ['purifier' => 5];

            $this->telegramBotHelper->sendNewMedalMessage($agent, $medalUps, $groupId);

            $io->success('Finished!');
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());
        }

        return 0;
    }
}
