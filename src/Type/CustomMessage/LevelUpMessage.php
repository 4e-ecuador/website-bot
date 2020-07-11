<?php

namespace App\Type\CustomMessage;

use App\Entity\Agent;
use App\Exception\EmojiNotFoundException;
use App\Service\EmojiService;
use App\Service\MedalChecker;
use App\Service\TelegramBotHelper;
use App\Type\AbstractCustomMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class LevelUpMessage extends AbstractCustomMessage
{
    private TranslatorInterface $translator;
    private MedalChecker $medalChecker;
    private EmojiService $emojiService;
    private Agent $agent;
    private int $level;
    private int $recursions;
    private string $pageBaseUrl;

    public function __construct(
        TelegramBotHelper $telegramBotHelper,
        EmojiService $emojiService,
        TranslatorInterface $translator,
        Agent $agent,
        MedalChecker $medalChecker,
        int $level,
        int $recursions,
        string $pageBaseUrl
    ) {
        parent::__construct($telegramBotHelper);
        $this->agent = $agent;
        $this->level = $level;
        $this->recursions = $recursions;
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
        $tada = $this->emojiService->getEmoji('tadaa')->getBytecode();

        $response = [];

        $response[] = $this->translator->trans('new.medal.header');

        $response[] = '[ ]('.$this->pageBaseUrl.'/build/images/badges/'
            .$this->medalChecker->getBadgePath('LevelUp_'.$this->level, 0).')';

        $response[] = $this->translator->trans(
            'new.level.text.1',
            [
                'agent' => $this->getAgentTelegramName($this->agent),
            ]
        );

        $response[] = '';

        if ($this->recursions) {
            $response[] = str_repeat('16+', $this->recursions);
        }

        $response[] = $this->translator->trans(
            'new.level.text.2',
            ['level' => $this->level]
        );

        $response[] = '';

        $response[] = $this->translator->trans(
            'new.medal.text.3',
            [
                'link' => sprintf(
                    '%s/stats/agent/%s',
                    $this->pageBaseUrl,
                    $this->agent->getId()
                ),
            ]
        );
        $response[] = '';
        $response[] = $this->translator->trans(
            'new.medal.text.4',
            [
                'tadaa' => $tada.$tada.$tada,
            ]
        );

        return $response;
    }
}
