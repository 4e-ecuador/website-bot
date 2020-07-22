<?php

namespace App\Type\CustomMessage;

use App\Entity\Agent;
use App\Exception\EmojiNotFoundException;
use App\Service\EmojiService;
use App\Service\MedalChecker;
use App\Type\AbstractCustomMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class MedalDoubleMessage extends AbstractCustomMessage
{
    private MedalChecker $medalChecker;
    private EmojiService $emojiService;
    private TranslatorInterface $translator;
    private string $pageBaseUrl;
    private Agent $agent;
    private array $medalDoubles;

    public function __construct(
        EmojiService $emojiService,
        TranslatorInterface $translator,
        MedalChecker $medalChecker,
        string $pageBaseUrl
    ) {
        $this->translator = $translator;
        $this->medalChecker = $medalChecker;
        $this->pageBaseUrl = $pageBaseUrl;
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
            'new.medal.text.1',
            [
                'medals' => count($this->medalDoubles),
                'agent'  => $this->getAgentTelegramName($this->agent),
            ]
        );

        $message[] = '';

        foreach ($this->medalDoubles as $medal => $level) {
            $message[] = $this->translator->trans(
                'new.medal.text.2',
                [
                    'medal'  => $medal,
                    'level'  => $this->medalChecker
                        ->translateMedalLevel(5),
                    'double' => 'X '.$level,
                ]
            );
        }

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
                'tadaa' => $tadaa.$tadaa.$tadaa,
            ]
        );

        return $message;
    }

    public function setAgent(Agent $agent): self
    {
        $this->agent = $agent;

        return $this;
    }

    public function setMedalDoubles(array $medalDoubles): self
    {
        $this->medalDoubles = $medalDoubles;

        return $this;
    }
}
