<?php

namespace App\Type\CustomMessage;

use App\Entity\Agent;
use App\Service\MedalChecker;
use App\Service\TelegramBotHelper;
use App\Type\AbstractCustomMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewMedalMessage extends AbstractCustomMessage
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Agent
     */
    private $agent;

    /**
     * @var array
     */
    private $medalUps;

    /**
     * @var MedalChecker
     */
    private $medalChecker;

    /**
     * @var string
     */
    private $pageBaseUrl;

    public function __construct(
        TelegramBotHelper $telegramBotHelper, TranslatorInterface $translator,
        Agent $agent, MedalChecker $medalChecker, array $medalUps, string $pageBaseUrl
    ) {
        $this->translator = $translator;
        $this->agent = $agent;
        $this->medalUps = $medalUps;
        $this->medalChecker = $medalChecker;
        $this->pageBaseUrl = $pageBaseUrl;

        parent::__construct($telegramBotHelper);
    }

    public function getMessage(): array
    {
        $tadaa = $this->telegramBotHelper->getEmoji('tadaa');

        $firstValue = reset($this->medalUps);
        $firstMedal = key($this->medalUps);

        $message = [];

        $message[] = $this->translator->trans('new.medal.header');

        // Medal image
        $message[] = '[ ]('.$this->pageBaseUrl.'/build/images/badges/'
            .$this->medalChecker->getBadgePath($firstMedal, $firstValue).')';

        $message[] = $this->translator->trans(
            'new.medal.text.1', [
                'medals' => count($this->medalUps),
                'agent'  => $this->getAgentTelegramName($this->agent),
            ]
        );

        $message[] = '';

        foreach ($this->medalUps as $medal => $level) {
            $message[] = $this->translator->trans(
                'new.medal.text.2', [
                    'medal' => $medal,
                    'level' => $this->medalChecker->translateMedalLevel($level),
                ]
            );
        }

        $message[] = '';
        $message[] = $this->translator->trans(
            'new.medal.text.3', [
                'link' => sprintf('%s/stats/agent/%s', $this->pageBaseUrl, $this->agent->getId()),
            ]
        );
        $message[] = '';
        $message[] = $this->translator->trans(
            'new.medal.text.4', [
                'tadaa' => $tadaa.$tadaa.$tadaa,
            ]
        );

        return $message;
    }
}
