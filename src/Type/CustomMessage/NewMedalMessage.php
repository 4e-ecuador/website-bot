<?php

namespace App\Type\CustomMessage;

use App\Entity\Agent;
use App\Exception\EmojiNotFoundException;
use App\Type\AbstractCustomMessage;

class NewMedalMessage extends AbstractCustomMessage
{
    private Agent $agent;
    private array $medalUps;

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
                'medals' => count($this->medalUps),
                'agent'  => $this->getAgentTelegramName($this->agent),
            ]
        );

        $message[] = '';

        foreach ($this->medalUps as $medal => $level) {
            $message[] = $this->translator->trans(
                'new.medal.text.2',
                [
                    'medal'  => $medal,
                    'level'  => $this->medalChecker
                        ->translateMedalLevel($level),
                    'double' => '',
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

    public function setMedalUps(array $medalUps): NewMedalMessage
    {
        $this->medalUps = $medalUps;

        return $this;
    }

    public function setAgent(Agent $agent): NewMedalMessage
    {
        $this->agent = $agent;

        return $this;
    }
}
