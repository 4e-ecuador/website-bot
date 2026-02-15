<?php

namespace App\Type\CustomMessage;

use App\Type\AbstractCustomMessage;
use DateTime;

class NotifyEventsMessage extends AbstractCustomMessage
{
    private bool $firstAnnounce;

    public function getMessage(bool $useLinks = true): array
    {
        $message = [];

        $ingressFS = $this->ingressEventRepository->findFutureFS();
        if ($ingressFS !== []) {
            $fsMessage = $this->buildFsMessage($ingressFS, $useLinks);
            if ($fsMessage === []) {
                return [];
            }

            $message = [...$message, ...$fsMessage];
        }

        $ingressMD = $this->ingressEventRepository->findFutureMD();
        if ($ingressMD !== []) {
            $message[] = 'HAY MD!!! - contacte un dev =;)';
            if ($this->firstAnnounce) {
                $message[] = 'YAY!!!';
            }
        }

        return $message;
    }

    /**
     * @param array<\App\Entity\IngressEvent> $ingressFS
     *
     * @return array<int, string>
     */
    private function buildFsMessage(array $ingressFS, bool $useLinks): array
    {
        $speaker = $this->emojiService->getEmoji('loudspeaker')->getBytecode();
        $headKey = $this->firstAnnounce
            ? 'notify.events.head.fs.first'
            : 'notify.events.head.fs';

        $links = $this->buildEventLinks($ingressFS, $useLinks);
        $lastEvent = $ingressFS[array_key_last($ingressFS)];
        $eventDate = $lastEvent->getDateStart();
        $daysRemaining = $eventDate->diff(new DateTime())->days;

        if ($daysRemaining > 8 && !$this->firstAnnounce) {
            return [];
        }

        $message = [];
        $message[] = $speaker.' '.$this->translator->trans($headKey);
        $message[] = $this->translator->trans(
            'notify.events.events.fs',
            ['links' => implode(', ', $links)]
        );
        $message[] = '';

        $msgExtra = $this->translator->trans('notify.events.events.fs.extra');
        if ($msgExtra !== '' && $msgExtra !== '0') {
            $message[] = $msgExtra;
            $message[] = '';
        }

        $message[] = '*'
            .$this->translator->trans(
                'notify.events.days.remaining',
                ['count' => $daysRemaining]
            )
            .'*';

        return $message;
    }

    /**
     * @param array<\App\Entity\IngressEvent> $events
     *
     * @return array<string>
     */
    private function buildEventLinks(array $events, bool $useLinks): array
    {
        $links = [];

        foreach ($events as $event) {
            $links[] = $useLinks
                ? sprintf('[%s](%s)', $event->getName(), $event->getLink())
                : $event->getName();
        }

        return $links;
    }

    public function setFirstAnnounce(bool $firstAnnounce): NotifyEventsMessage
    {
        $this->firstAnnounce = $firstAnnounce;

        return $this;
    }
}
