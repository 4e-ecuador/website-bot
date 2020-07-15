<?php

namespace App\Command;

use App\Exception\EmojiNotFoundException;
use App\Repository\AgentStatRepository;
use App\Service\EmojiService;
use App\Service\TelegramBotHelper;
use DateTime;
use DateTimeZone;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SendStatusCommand extends Command
{
    protected static $defaultName = 'sendStatus';// Type must be defined in base class :(

    private TelegramBotHelper $telegramBotHelper;
    private AgentStatRepository $agentStatRepository;
    private EmojiService $emojiService;
    private string $defaultTimeZone;

    public function __construct(
        TelegramBotHelper $telegramBotHelper,
        AgentStatRepository $agentStatRepository,
        EmojiService $emojiService,
        string $defaultTimeZone
    ) {
        parent::__construct();
        $this->telegramBotHelper = $telegramBotHelper;
        $this->agentStatRepository = $agentStatRepository;
        $this->defaultTimeZone = $defaultTimeZone;
        $this->emojiService = $emojiService;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Send status.');
    }

    /**
     * @throws EmojiNotFoundException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);
        $barChart = $this->emojiService->getEmoji('bar-chart');

        $io->writeln('Sending '.$barChart->getNative().' status update...');

        try {
            $groupId = $this->telegramBotHelper->getGroupId('test');

            $dateTime = new DateTime(
                'now -1 day',
                new DateTimeZone($this->defaultTimeZone)
            );
            $localDateTime = new DateTime(
                'now',
                new DateTimeZone($this->defaultTimeZone)
            );

            $statsCount = $this->agentStatRepository->findDayly($dateTime);

            $message = [];

            $message[] = $barChart->getBytecode().' *Status update* ';
            $message[] = 'Server: '.date('Y-m-d H:i:s');
            $message[] = 'Local : '.$localDateTime->format('Y-m-d H:i:s');
            $message[] = 'TZ: '.$this->defaultTimeZone;
            $message[] = '';
            $message[] = 'Stats for: *'.$dateTime->format('Y-m-d').'*';
            $message[] = '';
            $message[] = sprintf('Stats uploaded: `%d`', count($statsCount));

            $this->telegramBotHelper->sendMessage(
                $groupId,
                implode("\n", $message),
                true
            );

            $io->success('Finished!');
        } catch (Exception $exception) {
            $io->error($exception->getMessage());

            return 1;
        }

        return 0;
    }
}
