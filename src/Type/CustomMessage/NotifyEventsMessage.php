<?php

namespace App\Type\CustomMessage;

use App\Repository\IngressEventRepository;
use App\Service\TelegramBotHelper;
use App\Type\AbstractCustomMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotifyEventsMessage extends AbstractCustomMessage
{
    /**
     * @var IngressEventRepository
     */
    private $ingressEventRepository;

    /**
     * @var bool
     */
    private $firstAnnounce;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TelegramBotHelper $telegramBotHelper,
        IngressEventRepository $ingressEventRepository, TranslatorInterface $translator, bool $firstAnnounce)
    {
        $this->ingressEventRepository = $ingressEventRepository;
        $this->firstAnnounce = $firstAnnounce;
        $this->translator = $translator;

        parent::__construct($telegramBotHelper);
    }

    public function getMessage(): array
    {
        $message = [];
        $ingressFS = $this->ingressEventRepository->findFutureFS();
        $ingressMD = $this->ingressEventRepository->findFutureMD();

        if ($ingressFS) {
            if ($this->firstAnnounce) {
                $message[] = $this->translator->trans('notify.events.head.first');
            } else {
                $message[] = $this->translator->trans('notify.events.head');
            }

            $links = [];
            foreach ($ingressFS as $event) {
                $links[] = sprintf('[%s](%s)', $event->getName(), $event->getLink());
            }

            $message[] = $this->translator->trans('notify.events.events.fs', ['links' => implode(', ', $links)]);
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
