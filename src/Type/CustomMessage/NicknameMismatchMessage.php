<?php

namespace App\Type\CustomMessage;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Entity\User;
use App\Exception\EmojiNotFoundException;
use App\Service\EmojiService;
use App\Type\AbstractCustomMessage;

class NicknameMismatchMessage extends AbstractCustomMessage
{
    private EmojiService $emojiService;
    private string $announceAdminCc;

    private User $user;
    private Agent $agent;
    private AgentStat $statEntry;

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

        $message[] = str_repeat($redLight, 2)
            .'** Nickname mismatch **'
            .str_repeat($redLight, 2);
        $message[] = '';
        $message[] = 'We have detected a different nickname in uploaded stats!';
        $message[] = '';
        $message[] = 'Nick: '.$this->statEntry->getNickname();
        $message[] = '';
        $message = array_merge(
            $message,
            $this->getAgentUserData($this->agent, $this->user)
        );
        $message[] = '';
        $message[] = 'CC: '.$this->announceAdminCc;

        return $message;
    }

    public function setUser(User $user): NicknameMismatchMessage
    {
        $this->user = $user;

        return $this;
    }

    public function setAgent(Agent $agent): NicknameMismatchMessage
    {
        $this->agent = $agent;

        return $this;
    }

    public function setStatEntry(AgentStat $statEntry): NicknameMismatchMessage
    {
        $this->statEntry = $statEntry;

        return $this;
    }
}
