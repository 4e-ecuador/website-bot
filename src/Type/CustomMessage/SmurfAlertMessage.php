<?php

namespace App\Type\CustomMessage;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Entity\User;
use App\Exception\EmojiNotFoundException;
use App\Service\EmojiService;
use App\Type\AbstractCustomMessage;

class SmurfAlertMessage extends AbstractCustomMessage
{
    private EmojiService $emojiService;

    private User $user;
    private Agent $agent;
    private AgentStat $statEntry;
    private string $announceAdminCc;

    public function __construct(
        EmojiService $emojiService,
        string $announceAdminCc
    ) {
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

    /**
     * @param User $user
     *
     * @return SmurfAlertMessage
     */
    public function setUser(User $user): SmurfAlertMessage
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param Agent $agent
     *
     * @return SmurfAlertMessage
     */
    public function setAgent(Agent $agent): SmurfAlertMessage
    {
        $this->agent = $agent;

        return $this;
    }

    /**
     * @param AgentStat $statEntry
     *
     * @return SmurfAlertMessage
     */
    public function setStatEntry(AgentStat $statEntry): SmurfAlertMessage
    {
        $this->statEntry = $statEntry;

        return $this;
    }

    /**
     * @param string $announceAdminCc
     *
     * @return SmurfAlertMessage
     */
    public function setAnnounceAdminCc(string $announceAdminCc
    ): SmurfAlertMessage {
        $this->announceAdminCc = $announceAdminCc;

        return $this;
    }
}
