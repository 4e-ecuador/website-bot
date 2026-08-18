<?php

declare(strict_types=1);

namespace App\Type\CustomMessage;

use App\Exception\EmojiNotFoundException;
use App\Type\AbstractCustomMessage;

class NotifyUploadReminder extends AbstractCustomMessage
{
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
