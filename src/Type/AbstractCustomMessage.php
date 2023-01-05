<?php

namespace App\Type;

use App\Entity\Agent;
use App\Entity\User;
use App\Repository\IngressEventRepository;
use App\Service\EmojiService;
use App\Service\MedalChecker;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractCustomMessage
{
    public function __construct(
        protected EmojiService $emojiService,
        protected TranslatorInterface $translator,
        protected MedalChecker $medalChecker,
        protected IngressEventRepository $ingressEventRepository,
        #[Autowire('%env(ANNOUNCE_ADMIN_CC)%')] protected string $announceAdminCc,
        #[Autowire('%env(PAGE_BASE_URL)%')] protected string $pageBaseUrl,
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
