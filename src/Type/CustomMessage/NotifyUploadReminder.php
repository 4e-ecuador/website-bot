<?php

namespace App\Type\CustomMessage;

use App\Service\TelegramBotHelper;
use App\Type\AbstractCustomMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotifyUploadReminder extends AbstractCustomMessage
{
    private TranslatorInterface $translator;

    public function __construct(
        TelegramBotHelper $telegramBotHelper,
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;

        parent::__construct($telegramBotHelper);
    }

    public function getMessage(): array
    {
        $message = [];

        $message[] = $this->translator->trans('notify.upload.reminder');

        return $message;
    }
}
