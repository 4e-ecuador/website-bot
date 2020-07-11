<?php

namespace App\Type\CustomMessage;

use App\Repository\IngressEventRepository;
use App\Service\TelegramBotHelper;
use App\Type\AbstractCustomMessage;
use DateTime;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotifyEventsMessage extends AbstractCustomMessage
{
    private IngressEventRepository $ingressEventRepository;

    private bool $firstAnnounce;

    private TranslatorInterface $translator;

    public function __construct(
        TelegramBotHelper $telegramBotHelper,
        IngressEventRepository $ingressEventRepository,
        TranslatorInterface $translator,
        bool $firstAnnounce
    ) {
        $this->ingressEventRepository = $ingressEventRepository;
        $this->firstAnnounce = $firstAnnounce;
        $this->translator = $translator;

        parent::__construct($telegramBotHelper);
    }

    public function getMessage($useLinks = true): array
    {
        $message = [];
        $ingressFS = $this->ingressEventRepository->findFutureFS();
        $ingressMD = $this->ingressEventRepository->findFutureMD();
        $sendDaysBeforeEvent = 8;

        if ($ingressFS) {
            if ($this->firstAnnounce) {
                $message[] = $this->translator->trans(
                    'notify.events.head.fs.first'
                );
            } else {
                $message[] = $this->translator->trans('notify.events.head.fs');
            }

            $links = [];
            foreach ($ingressFS as $event) {
                $links[] = $useLinks
                    ? sprintf(
                        '[%s](%s)',
                        $event->getName(),
                        $event->getLink()
                    )
                    : $event->getName();
                $eventDate = $event->getDateStart();
            }

            $daysRemaining = $eventDate->diff(new DateTime())->days;

            // TODO wtf?
            $daysRemaining += 1;

            if ($daysRemaining > $sendDaysBeforeEvent) {
                return [];
            }

            $message[] = $this->translator->trans(
                'notify.events.events.fs',
                ['links' => implode(', ', $links)]
            );
            $message[] = '';

            $msgExtra = $this->translator->trans(
                'notify.events.events.fs.extra'
            );

            if ($msgExtra) {
                $message[] = $msgExtra;
                $message[] = '';
            }

            $message[] = '*'
                .$this->translator->trans(
                    'notify.events.days.remaining',
                    ['count' => $daysRemaining]
                )
                .'*';
        }

        if ($ingressMD) {
            $message[] = 'HAY MD!!! - contacte un dev =;)';
            if ($this->firstAnnounce) {
                // $io->success('FIRST');
            }
        }

        return $message;
    }
}
