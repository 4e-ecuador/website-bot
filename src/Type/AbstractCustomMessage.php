<?php

namespace App\Type;

use App\Entity\Agent;
use App\Entity\User;
use App\Repository\IngressEventRepository;
use App\Service\EmojiService;
use App\Service\MedalChecker;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractCustomMessage
{
    public function __construct(
        protected EmojiService $emojiService,
        protected TranslatorInterface $translator,
        protected MedalChecker $medalChecker,
        protected IngressEventRepository $ingressEventRepository,
        protected string $pageBaseUrl,
        protected string $announceAdminCc
    ) {
    }

    /**
     * @return array<int, string>
     */
    abstract public function getMessage(): array;

    public function getText(): string
    {
        return implode("\n", $this->getMessage());
    }

    protected function getAgentTelegramName(Agent $agent): string
    {
        return str_replace(
            '_',
            '\\_',
            $agent->getTelegramName()
                ?: $agent->getNickname()
        );
    }

    /**
     * @return array<int, string>
     */
    protected function getAgentUserData(Agent $agent, User $user): array
    {
        $message = [];
        $message[] = 'Agent: '.$agent->getNickname();
        $message[] = 'ID: '.$agent->getId();
        $message[] = '';
        $message[] = 'User: '.$user->getUserAgentName();
        $message[] = 'ID: '.$user->getId();
        $message[] = '';
        $message[] = 'Please verify!';

        return $message;
    }
}
