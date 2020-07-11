<?php

namespace App\Type\CustomMessage;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Entity\User;
use App\Exception\EmojiNotFoundException;
use App\Service\EmojiService;
use App\Service\TelegramBotHelper;
use App\Type\AbstractCustomMessage;

class SmurfAlertMessage extends AbstractCustomMessage
{
    private User $user;
    private Agent $agent;
    private EmojiService $emojiService;
    private AgentStat $statEntry;
    private string $announceAdminCc;

    public function __construct(
        TelegramBotHelper $telegramBotHelper,
        EmojiService $emojiService,
        User $user,
        Agent $agent,
        AgentStat $statEntry,
        string $announceAdminCc
    ) {
        parent::__construct($telegramBotHelper);

        $this->user = $user;
        $this->agent = $agent;
        $this->statEntry = $statEntry;
        $this->announceAdminCc = $announceAdminCc;
        $this->emojiService = $emojiService;
    }

    /**
     * @throws EmojiNotFoundException
     */
    public function getMessage(): array
    {
        $redLight = $this->emojiService->getEmoji('redlight')->getBytecode();
        $message = [];

        $message[] = str_repeat($redLight, 3)
            .'** SMURF ALERT !!! **'
            .str_repeat($redLight, 3);
        $message[] = '';
        $message[] = 'We have detected an agent with the faction: '
            .$this->statEntry->getFaction();
        $message[] = '';
        $message = array_merge(
            $message,
            $this->getAgentUserData($this->agent, $this->user)
        );
        $message[] = '';
        $message[] = 'CC: '.$this->announceAdminCc;

        return $message;
    }
}
