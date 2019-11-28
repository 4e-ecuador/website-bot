<?php

namespace App\Command;

use App\Repository\EventRepository;
use App\Service\TelegramBotHelper;
use DateTime;
use IntlDateFormatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendNewsletterCommand extends Command
{
    protected static $defaultName = 'app:send:newsletter';

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var TelegramBotHelper
     */
    private $telegramBotHelper;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    public function __construct(TelegramBotHelper $telegramBotHelper, EventRepository $eventRepository, UrlGeneratorInterface $router)
    {
        $this->eventRepository = $eventRepository;
        $this->telegramBotHelper = $telegramBotHelper;
        $this->router = $router;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Send a newsletter')
            ->addOption('group', null, InputOption::VALUE_OPTIONAL, 'Group name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $groupId = $_ENV['ANNOUNCE_GROUP_ID_1'];

        if ($input->getOption('group')) {
            if ('test' === $input->getOption('group')) {
                $groupId = $_ENV['ANNOUNCE_GROUP_ID_TEST'];
            } else {
                throw new \UnexpectedValueException('Unknown group');
            }

            $io->writeln('group set to: '.$input->getOption('group'));
        }

        $message = [];

        $timeZone = new \DateTimeZone($_ENV['DEFAULT_TIMEZONE']);

        $dateNow = new DateTime('now', $timeZone);

        $context = $this->router->getContext();
        $context->setHost(str_replace('http://', '', $_ENV['PAGE_BASE_URL']));
        // $context->setBaseUrl('my/path');

        $formatterDate = new IntlDateFormatter(
            'es',
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            $_ENV['DEFAULT_TIMEZONE'],
            IntlDateFormatter::GREGORIAN,
            'd \'de\' MMMM \'de\' y'
        );

        $formatterDateFull = new IntlDateFormatter(
            'es',
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            $_ENV['DEFAULT_TIMEZONE'],
            IntlDateFormatter::GREGORIAN
        );

        $fsDate = $this->getNextFS($timeZone);
        $events = $this->eventRepository->findAll();
        $currentEvents = [];
        $futureEvents = [];

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
        $message[] = '(Links a las paginas de los eventos - TBD)';

        $message[] = '';
        $message[] = '*Eventos 4E*';

        if ($currentEvents) {
            $message[] = '';
            $message[] = 'Eventos actuales:';
            foreach ($currentEvents as $event) {
                $link = $this->router->generate('event_show', ['id' => $event->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
                $message[] = sprintf(
                    '- [%s](%s) (tipo: *%s*) termina el dia %s',
                    $event->getName(),
                    $link,
                    $event->getEventType(),
                    $formatterDateFull->format($event->getDateEnd())//->format('Y-m-d H:i:s')
                );
            }
        } else {
            $message[] = 'Actualmente no hay eventos :(';
        }

        if ($futureEvents) {
            $message[] = '';
            $message[] = 'Eventos futuros:';
            foreach ($futureEvents as $event) {
                $link = $this->router->generate('event_show', ['id' => $event->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
                $message[] = sprintf(
                    '- [%s](%s) (tipo: *%s*) empieza el dia %s',
                    $event->getName(),
                    $link,
                    $event->getEventType(),
                    $formatterDateFull->format($event->getDateStart())
                );
            }
        }

        $statsImportPage = $this->router->generate('stat_import', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $message[] = '';
        $message[] = sprintf('*No te olvides de* [subir tus estadisticas](%s)*!*', $statsImportPage);
        $message[] = '';
        $message[] = 'In Jarvis we trust!';
        $message[] = '';

        // $io->writeln($message);

        $this->telegramBotHelper->sendMessage($groupId, implode("\n", $message));

        $io->success('Finished!');

        return 0;
    }

    private function getNextFS(\DateTimeZone $timeZone): DateTime
    {
        $dateNow = new DateTime('now');
        $fsThisMonth = new DateTime('first saturday of this month', $timeZone);
        $fsNextMonth = new DateTime('first saturday of next month', $timeZone);

        return ($dateNow > $fsThisMonth) ? $fsNextMonth : $fsThisMonth;
    }
}
