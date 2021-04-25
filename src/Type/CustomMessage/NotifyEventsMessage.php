<?php

namespace App\Type\CustomMessage;

use App\Type\AbstractCustomMessage;
use DateTime;

class NotifyEventsMessage extends AbstractCustomMessage
{
    private bool $firstAnnounce;

    public function getMessage($useLinks = true): array
    {
        $speaker = $this->emojiService->getEmoji('loudspeaker')->getBytecode();

        $message = [];
        $ingressFS = $this->ingressEventRepository->findFutureFS();
        $ingressMD = $this->ingressEventRepository->findFutureMD();
        $sendDaysBeforeEvent = 8;
        if ($ingressFS) {
            if ($this->firstAnnounce) {
                $message[] = $speaker.' '.$this->translator->trans(
                        'notify.events.head.fs.first'
                    );
            } else {
                $message[] = $speaker.' '.$this->translator->trans(
                        'notify.events.head.fs'
                    );
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

            if (!isset($eventDate)) {
                return [];
            }

            $daysRemaining = $eventDate->diff(new DateTime())->days;

            // TODO wtf?
            // ++$daysRemaining;

            if ($daysRemaining > $sendDaysBeforeEvent && ! $this->firstAnnounce) {
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
                $message[] = 'YAY!!!';
                // $io->success('FIRST');
            }
        }

        return $message;
    }

    public function setFirstAnnounce(bool $firstAnnounce): NotifyEventsMessage
    {
        $this->firstAnnounce = $firstAnnounce;

        return $this;
    }
}
