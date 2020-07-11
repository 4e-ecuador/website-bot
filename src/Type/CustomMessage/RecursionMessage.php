<?php

namespace App\Type\CustomMessage;

use App\Entity\Agent;
use App\Exception\EmojiNotFoundException;
use App\Service\EmojiService;
use App\Service\TelegramBotHelper;
use App\Type\AbstractCustomMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class RecursionMessage extends AbstractCustomMessage
{
    private Agent $agent;
    private int $recursions;
    private EmojiService $emojiService;
    private TranslatorInterface $translator;
    private string $pageBaseUrl;

    public function __construct(
        TelegramBotHelper $telegramBotHelper,
        EmojiService $emojiService,
        TranslatorInterface $translator,
        Agent $agent,
        int $recursions,
        string $pageBaseUrl
    ) {
        parent::__construct($telegramBotHelper);
        $this->agent = $agent;
        $this->recursions = $recursions;
        $this->translator = $translator;
        $this->pageBaseUrl = $pageBaseUrl;
        $this->emojiService = $emojiService;
    }

    /**
     * @throws EmojiNotFoundException
     */
    public function getMessage(): array
    {
        $tadaa = $this->emojiService->getEmoji('tadaa')->getBytecode();
        $message = [];

        $message[] = $this->translator->trans('recursion.header');
        $message[] = '[ ]('.$this->pageBaseUrl
            .'/build/images/badges/UniqueBadge_Simulacrum.png)';
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
}
