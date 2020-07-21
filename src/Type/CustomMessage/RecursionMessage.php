<?php

namespace App\Type\CustomMessage;

use App\Entity\Agent;
use App\Exception\EmojiNotFoundException;
use App\Service\EmojiService;
use App\Type\AbstractCustomMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class RecursionMessage extends AbstractCustomMessage
{
    private EmojiService $emojiService;
    private TranslatorInterface $translator;

    private Agent $agent;
    private int $recursions;

    public function __construct(
        EmojiService $emojiService,
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
        $this->emojiService = $emojiService;
    }

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

    /**
     * @param Agent $agent
     *
     * @return RecursionMessage
     */
    public function setAgent(Agent $agent): RecursionMessage
    {
        $this->agent = $agent;

        return $this;
    }

    /**
     * @param int $recursions
     *
     * @return RecursionMessage
     */
    public function setRecursions(int $recursions): RecursionMessage
    {
        $this->recursions = $recursions;

        return $this;
    }
}
