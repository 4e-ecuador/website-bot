<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\FsData;
use App\Entity\IngressEvent;
use App\Repository\AgentRepository;
use App\Service\HtmlParser;
use App\Type\AgentFsInfo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;

#[AsCommand(
    name: 'app:checkfs',
    description: 'Check FS worldwide'
)]
final class CheckFsCommand extends Command
{
    public function __construct(
        private readonly HtmlParser $htmlParser,
        private readonly AgentRepository $agentRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $url = 'https://fevgames.net/ifs/events';
        $events = [];
        $agents = [];

        $io = new SymfonyStyle($input, $output);

        $io->title('Scraping: '.$url);

        $html = (string)file_get_contents($url);

        $crawler = new Crawler($html);

        $nodeValues = $crawler->filter('table tbody tr td#city')->each(
            static fn(Crawler $node, $i) => $node
        );

        $io->text(sprintf('Found %d locations', count($nodeValues)));

        $progressBar = new ProgressBar($output, count($nodeValues));

        $progressBar->setFormat(
            ' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%'
        );

        $progressBar->start();

        foreach ($nodeValues as $node) {
            $a = $node->filter('a');

            $location = $a->text();
            $link = (string)$a->attr('href');

            $eventId = (int)preg_replace('/\D/', '', $link);

            $event = (new IngressEvent)
                ->setLink($url.'/'.$link)
                ->setName($location);

            $events[] = $event;

            try {
                $info = $this->htmlParser->getFsAssistants($event);

                foreach ($info->poc as $faction => $agentNick) {
                    if (array_key_exists($agentNick, $agents)) {
                        // throw new \UnexpectedValueException(
                        //     'Agent reg twice: '.$agentNick
                        // );
                    }

                    $agent = new AgentFsInfo(
                        nickname: $agentNick,
                        faction: $faction,
                        role: 'poc',
                        location: $location,
                        eventId: $eventId,
                    );

                    $agents[$agentNick] = $agent;
                }

                foreach ($info->attendees as $faction => $attendees) {
                    foreach ($attendees as $attendee) {
                        if ($attendee) {
                            if (array_key_exists($attendee, $agents)) {
                                //     throw new \UnexpectedValueException(
                                //         'Agent reg twice: '.$attendee
                                //     );
                            }

                            $agent = new AgentFsInfo(
                                nickname: $attendee,
                                faction: $faction,
                                role: 'attendee',
                                location: $location,
                                eventId: $eventId,
                            );

                            $agents[$attendee] = $agent;
                        }
                    }
                }
            } catch (\Exception $exception) {
                $io->error($exception->getMessage().$event->getLink());
            }

            $progressBar->advance();
        }

        $progressBar->finish();

        $systemAgents = $this->agentRepository->findAll();

        $detectedAgents = [];

        foreach ($systemAgents as $systemAgent) {
            if (array_key_exists($systemAgent->getNickname(), $agents)) {
                $detectedAgents[] = $agents[$systemAgent->getNickname()];
            }
        }

        $io->text('');
        $io->text('');

        // $table = new Table($output);
        // $table->setHeaders(['Nickname', 'Faction', 'Role', 'Location']);
        //
        // foreach ($agents as $agent) {
        //     $table->addRow([
        //         $agent->nickname,
        //         $agent->faction,
        //         $agent->role,
        //         $agent->location,
        //     ]);
        // }
        //
        // $table->render();

        $io->text(sprintf('Found %d agents worldwide.', count($agents)));

        $table = new Table($output);
        $table->setHeaders(['Nickname', 'Faction', 'Role', 'Location']);

        foreach ($detectedAgents as $agent) {
            $table->addRow([
                $agent->nickname,
                $agent->faction,
                $agent->role,
                $agent->location,
            ]);
        }

        $table->render();

        $fsInfo = new \stdClass();

        $fsInfo->events = $events;
        $fsInfo->agents = $agents;

        $fsData = (new FsData())
            ->setAttendeesCount(count($agents))
            ->setData((string)json_encode($fsInfo));

        $this->entityManager->persist($fsData);
        $this->entityManager->flush();

        return self::SUCCESS;
    }
}
