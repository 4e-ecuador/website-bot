<?php

namespace App\Type\CustomMessage;

use App\Entity\Agent;
use App\Exception\EmojiNotFoundException;
use App\Service\EmojiService;
use App\Service\MedalChecker;
use App\Service\TelegramBotHelper;
use App\Type\AbstractCustomMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewMedalMessage extends AbstractCustomMessage
{
    private MedalChecker $medalChecker;
    private EmojiService $emojiService;
    private TranslatorInterface $translator;
    private Agent $agent;
    private array $medalUps;
    private string $pageBaseUrl;

    public function __construct(
        TelegramBotHelper $telegramBotHelper,
        EmojiService $emojiService,
        TranslatorInterface $translator,
        Agent $agent,
        MedalChecker $medalChecker,
        array $medalUps,
        string $pageBaseUrl
    ) {
        parent::__construct($telegramBotHelper);

        $this->translator = $translator;
        $this->agent = $agent;
        $this->medalUps = $medalUps;
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

        $firstValue = reset($this->medalUps);
        $firstMedal = key($this->medalUps);

        $message = [];

        $message[] = $this->translator->trans('new.medal.header');

        // Medal image
        $message[] = '[ ]('.$this->pageBaseUrl.'/build/images/badges/'
            .$this->medalChecker->getBadgePath($firstMedal, $firstValue).')';

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
                    'medal' => $medal,
                    'level' => $this->medalChecker->translateMedalLevel($level),
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
}
