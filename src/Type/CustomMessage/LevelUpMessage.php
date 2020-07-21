<?php

namespace App\Type\CustomMessage;

use App\Entity\Agent;
use App\Exception\EmojiNotFoundException;
use App\Service\EmojiService;
use App\Type\AbstractCustomMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class LevelUpMessage extends AbstractCustomMessage
{
    private TranslatorInterface $translator;
    private EmojiService $emojiService;
    private string $pageBaseUrl;

    private Agent $agent;
    private int $level;
    private int $recursions;

    public function __construct(
        EmojiService $emojiService,
        TranslatorInterface $translator,
        string $pageBaseUrl
    ) {
        $this->translator = $translator;
        $this->pageBaseUrl = $pageBaseUrl;
        $this->emojiService = $emojiService;
    }

    /**
     * @throws EmojiNotFoundException
     */
    public function getMessage(): array
    {
        $tada = $this->emojiService->getEmoji('tadaa')->getBytecode();
        $speaker = $this->emojiService->getEmoji('loudspeaker')->getBytecode();

        $message = [];

        $message[] = $speaker.' '
            .$this->translator->trans('announce.header')
            .' '.$speaker;
        $message[] = '';

        $message[] = $this->translator->trans(
            'new.level.text.1',
            [
                'agent' => $this->getAgentTelegramName($this->agent),
            ]
        );

        $message[] = '';

        if ($this->recursions) {
            $message[] = str_repeat('16+', $this->recursions);
        }

        $message[] = $this->translator->trans(
            'new.level.text.2',
            ['level' => $this->level]
        );

        $message[] = '';

        $message[] = $this->translator->trans(
            'new.medal.text.3',
            [
                'link' => sprintf(
                    '%s/stats/agent/%s',
                    $this->pageBaseUrl,
                    $this->agent->getId()
                ),
            ]
        );
        $message[] = '';
        $message[] = $this->translator->trans(
            'new.medal.text.4',
            [
                'tadaa' => $tada.$tada.$tada,
            ]
        );

        return $message;
    }

    public function setAgent(Agent $agent): LevelUpMessage
    {
        $this->agent = $agent;

        return $this;
    }

    public function setLevel(int $level): LevelUpMessage
    {
        $this->level = $level;

        return $this;
    }

    public function setRecursions(int $recursions): LevelUpMessage
    {
        $this->recursions = $recursions;

        return $this;
    }
}
