<?php

namespace App\Type\CustomMessage;

use App\Service\EmojiService;
use App\Service\TelegramBotHelper;
use App\Type\AbstractCustomMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotifyUploadReminder extends AbstractCustomMessage
{
    private TranslatorInterface $translator;
    private EmojiService $emojiService;

    public function __construct(
        TelegramBotHelper $telegramBotHelper,
        TranslatorInterface $translator,
        EmojiService $emojiService
    ) {
        parent::__construct($telegramBotHelper);

        $this->translator = $translator;
        $this->emojiService = $emojiService;
    }

    public function getMessage(): array
    {
        $bulb = $this->emojiService->getEmoji('light-bulb')->getBytecode();
        $message = [];

        $message[] = $bulb.' '.$this->translator->trans('notify.upload.reminder');

        return $message;
    }
}
