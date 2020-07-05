<?php

namespace App\Command;

use App\Repository\AgentStatRepository;
use App\Service\TelegramBotHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SendStatusCommand extends Command
{
    protected static $defaultName = 'sendStatus';
    /**
     * @var TelegramBotHelper
     */
    private $telegramBotHelper;

    /**
     * @var AgentStatRepository
     */
    private $agentStatRepository;
    private string $defaultTimeZone;

    public function __construct(TelegramBotHelper $telegramBotHelper, AgentStatRepository $agentStatRepository, string $defaultTimeZone)
    {
        parent::__construct();
        $this->telegramBotHelper = $telegramBotHelper;
        $this->agentStatRepository = $agentStatRepository;
        $this->defaultTimeZone = $defaultTimeZone;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->writeln('Sending status update...');

        try {
            $groupId = $this->telegramBotHelper->getGroupId('admin');

            $dateTime = new \DateTime('now -1 day', new \DateTimeZone($this->defaultTimeZone));
            $localDateTime = new \DateTime('now', new \DateTimeZone($this->defaultTimeZone));

            $statsCount = $this->agentStatRepository->findDayly($dateTime);

            $message = [];

            $message[] = 'Status update: '.date('Y-m-d H:i:s');
            $message[] = 'Local time: '.$localDateTime->format('Y-m-d H:i:s');
            $message[] = 'Timezone: '.$this->defaultTimeZone;
            $message[] = 'Stats for: *'.$dateTime->format('Y-m-d').'*';
            $message[] = '';
            $message[] = sprintf('Stats uploaded: %d', count($statsCount));

            $this->telegramBotHelper->sendMessage($groupId, implode("\n", $message),true);

            $io->success('Finished!');
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());

            return 1;
        }

        return 0;
    }
}
