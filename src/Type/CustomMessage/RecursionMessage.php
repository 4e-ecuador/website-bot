<?php

namespace App\Type\CustomMessage;

use App\Entity\Agent;
use App\Exception\EmojiNotFoundException;
use App\Type\AbstractCustomMessage;

class RecursionMessage extends AbstractCustomMessage
{
    private Agent $agent;

    private int $recursions;

    /**
     * @throws EmojiNotFoundException
     */
    public function getMessage(): array
    {
        $tadaa = $this->emojiService->getEmoji('tadaa')->getBytecode();
        $speaker = $this->emojiService->getEmoji('loudspeaker')->getBytecode();
        $message = [];

        $message[] = $speaker.' '
            .$this->translator->trans('announce.header')
            .' '.$speaker;
        $message[] = '';
        $message[] = $this->translator->trans(
            'recursion.text.1',
            [
                'agent' => $this->getAgentTelegramName($this->agent),
            ]
        );
        $message[] = '';

        if ($this->recursions > 1) {
            $message[] = 'X '.$this->recursions;
            $message[] = '';
        }

        $message[] = $this->translator->trans(
            'new.medal.text.4',
            [
                'tadaa' => $tadaa.$tadaa.$tadaa,
            ]
        );
        $message[] = '';

        return $message;
    }

    public function setAgent(Agent $agent): RecursionMessage
    {
        $this->agent = $agent;

        return $this;
    }

    public function setRecursions(int $recursions): RecursionMessage
    {
        $this->recursions = $recursions;

        return $this;
    }
}
