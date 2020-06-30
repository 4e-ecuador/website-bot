<?php

namespace App\Type\CustomMessage;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Entity\User;
use App\Service\TelegramBotHelper;
use App\Type\AbstractCustomMessage;

class SmurfAlertMessage extends AbstractCustomMessage
{
    private User $user;

    private Agent $agent;

    private AgentStat $statEntry;

    private string $announceAdminCc;

    public function __construct(
        TelegramBotHelper $telegramBotHelper,
        User $user,
        Agent $agent,
        AgentStat $statEntry,
        string $announceAdminCc
    ) {
        $this->user = $user;
        $this->agent = $agent;
        $this->statEntry = $statEntry;
        $this->announceAdminCc = $announceAdminCc;

        parent::__construct($telegramBotHelper);
    }

    public function getMessage(): array
    {
        $redLight = $this->telegramBotHelper->getEmoji('redlight');
        $message = [];

        $message[] = str_repeat($redLight, 3)
            .'** SMURF ALERT !!! **'
            .str_repeat($redLight, 3);
        $message[] = '';
        $message[] = 'We have detected an agent with the faction: '
            .$this->statEntry->getFaction();
        $message[] = '';
        $message[] = 'Agent: '.$this->agent->getNickname();
        $message[] = 'ID: '.$this->agent->getId();
        $message[] = '';
        $message[] = 'User: '.$this->user->getUsername();
        $message[] = 'ID: '.$this->user->getId();
        $message[] = '';
        $message[] = 'Please verify!';
        $message[] = '';
        $message[] = 'CC: '.$this->announceAdminCc;

        return $message;
    }
}
