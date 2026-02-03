<?php

namespace App\Command;

use App\Repository\EventRepository;
use App\Repository\IngressEventRepository;
use App\Service\TelegramBotHelper;
use DateTime;
use DateTimeZone;
use Exception;
use IntlDateFormatter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use TelegramBot\Api\InvalidArgumentException;
use UnexpectedValueException;

#[AsCommand(
    name: 'app:send:newsletter',
    description: 'Send a newsletter'
)]
class SendNewsletterCommand extends Command
{

    public function __construct(
        private readonly TelegramBotHelper $telegramBotHelper,
        private readonly EventRepository $eventRepository,
        private readonly IngressEventRepository $ingressEventRepository,
        private readonly UrlGeneratorInterface $router,
        #[Autowire('%env(PAGE_BASE_URL)%')] private readonly string $pageBaseUrl,
        #[Autowire('%env(DEFAULT_TIMEZONE)%')] private readonly string $defaultTimeZone,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'group',
                null,
                InputOption::VALUE_OPTIONAL,
                'Group name'
            );
    }

    /**
     * @throws \TelegramBot\Api\Exception
     * @throws InvalidArgumentException
     * @throws Exception
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);
        $groupId = $this->telegramBotHelper->getGroupId();

        if ($input->getOption('group')) {
            if ('test' === $input->getOption('group')) {
                $groupId = $this->telegramBotHelper->getGroupId('test');
            } else {
                throw new UnexpectedValueException('Unknown group');
            }

            $io->writeln('group set to: '.$input->getOption('group'));
        }

        $message = [];

        $timeZone = new DateTimeZone($this->defaultTimeZone);

        $dateNow = new DateTime('now', $timeZone);

        $context = $this->router->getContext();
        $context->setHost(
            str_replace(['https://', 'http://'], '', $this->pageBaseUrl)
        );

        $formatterDate = new IntlDateFormatter(
            'es',
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            $this->defaultTimeZone,
            IntlDateFormatter::GREGORIAN,
            "d 'de' MMMM 'de' y"
        );

        $formatterDateFull = new IntlDateFormatter(
            'es',
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            $this->defaultTimeZone,
            IntlDateFormatter::GREGORIAN
        );

        $fsDate = $this->getNextFS($timeZone);
        $events = $this->eventRepository->findAll();
        $currentEvents = [];
        $futureEvents = [];

        $ingressFS = $this->ingressEventRepository->findFutureFS();
        $this->ingressEventRepository->findFutureMD();

        $fsStrings = [];

        foreach ($ingressFS as $fs) {
            $fsStrings[] = sprintf('[%s](%s)', $fs->getName(), $fs->getLink());
        }

        foreach ($events as $event) {
            if ($event->getDateStart() > $dateNow) {
                $futureEvents[] = $event;
            } elseif ($event->getDateEnd() < $dateNow) {
                // $pastEvents[] = $event;
            } else {
                $currentEvents[] = $event;
            }
        }

        $message[] = '===========================';
        $message[] = '*Boletin 4E*';
        $message[] = $formatterDate->format($dateNow);
        $message[] = '===========================';
        $message[] = '';
        $message[] = 'Buenos dias agentes!';
        $message[] = '';
        $message[] = '(blabla...?)';
        $message[] = '';
        $message[] = '*Eventos Ingress*';
        $message[] = '';

        $message[] = sprintf(
            'Proximo FS: %s - Faltan %d dias!!!',
            $formatterDate->format($fsDate),
            $dateNow->diff($fsDate)->format('%a')
        // $interval->format('%a')
        );

        $message[] = '';
        // '(Links a las paginas de los eventos - TBD)';
        $message[] = 'Lugares: '.implode(', ', $fsStrings);

        $message[] = '';
        $message[] = '*Eventos 4E*';

        if ($currentEvents !== []) {
            $message[] = '';
            $message[] = 'Eventos actuales:';
            foreach ($currentEvents as $event) {
                $link = $this->router->generate(
                    'event_show',
                    ['id' => $event->getId()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                /**
                 * @var DateTime $date
                 */
                $date = $event->getDateEnd();

                $message[] = sprintf(
                    '- [%s](%s) (tipo: *%s*) termina el dia %s',
                    $event->getName(),
                    $link,
                    $event->getEventType(),
                    $formatterDateFull->format(
                        $date->setTimezone($timeZone)
                    )
                );
            }
        } else {
            $message[] = 'Actualmente no hay eventos :(';
        }

        if ($futureEvents !== []) {
            $message[] = '';
            $message[] = 'Eventos futuros:';
            foreach ($futureEvents as $event) {
                $link = $this->router->generate(
                    'event_show',
                    ['id' => $event->getId()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $lll = new DateTime(
                    $event->getDateStart()->format('Y-m-d H:i:s'), $timeZone
                );
                $message[] = sprintf(
                    '- [%s](%s) (tipo: *%s*) empieza el dia %s',
                    $event->getName(),
                    $link,
                    $event->getEventType(),
                    $formatterDateFull->format($lll)
                // $formatterDateFull->format($event->getDateStart()->setTimezone($timeZone))
                );
            }
        }

        $statsImportPage = $this->router->generate(
            'stat_import',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $message[] = '';
        $message[] = sprintf(
            '*No te olvides de* [subir tus estadisticas](%s)*!*',
            $statsImportPage
        );
        $message[] = '';
        $message[] = 'In Jarvis we trust!';
        $message[] = '';

        $this->telegramBotHelper->sendMessage(
            $groupId,
            implode("\n", $message)
        );

        $io->success('Finished!');

        return Command::SUCCESS;
    }

    private function getNextFS(DateTimeZone $timeZone): DateTime
    {
        $dateNow = new DateTime('now');
        $fsThisMonth = new DateTime('first saturday of this month', $timeZone);
        $fsNextMonth = new DateTime('first saturday of next month', $timeZone);

        return ($dateNow > $fsThisMonth) ? $fsNextMonth : $fsThisMonth;
    }
}
