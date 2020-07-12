<?php

namespace App\Type\CustomMessage;

use App\Repository\IngressEventRepository;
use App\Service\EmojiService;
use App\Service\TelegramBotHelper;
use App\Type\AbstractCustomMessage;
use DateTime;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotifyEventsMessage extends AbstractCustomMessage
{
    private IngressEventRepository $ingressEventRepository;
    private EmojiService $emojiService;
    private TranslatorInterface $translator;
    private bool $firstAnnounce;

    public function __construct(
        TelegramBotHelper $telegramBotHelper,
        IngressEventRepository $ingressEventRepository,
        EmojiService $emojiService,
        TranslatorInterface $translator,
        bool $firstAnnounce
    ) {
        parent::__construct($telegramBotHelper);

        $this->ingressEventRepository = $ingressEventRepository;
        $this->firstAnnounce = $firstAnnounce;
        $this->translator = $translator;
        $this->emojiService = $emojiService;
    }

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
            ++$daysRemaining;

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
                $message[] = 'YAY!!!';
                // $io->success('FIRST');
            }
        }

        return $message;
    }
}
