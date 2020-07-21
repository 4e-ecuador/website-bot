<?php

namespace App\Type\CustomMessage;

use App\Exception\EmojiNotFoundException;
use App\Service\EmojiService;
use App\Type\AbstractCustomMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotifyUploadReminder extends AbstractCustomMessage
{
    private TranslatorInterface $translator;
    private EmojiService $emojiService;

    public function __construct(
        TranslatorInterface $translator,
        EmojiService $emojiService
    ) {
        $this->translator = $translator;
        $this->emojiService = $emojiService;
    }

    /**
     * @throws EmojiNotFoundException
     */
    public function getMessage(): array
    {
        $bulb = $this->emojiService->getEmoji('light-bulb')->getBytecode();
        $message = [];

        $message[] = $bulb.' '.$this->translator->trans(
                'notify.upload.reminder'
            );

        return $message;
    }
}
