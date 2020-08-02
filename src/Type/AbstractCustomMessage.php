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
    protected EmojiService $emojiService;
    protected TranslatorInterface $translator;
    protected MedalChecker $medalChecker;
    protected IngressEventRepository $ingressEventRepository;
    protected string $pageBaseUrl;
    protected string $announceAdminCc;

    public function __construct(
        EmojiService $emojiService,
        TranslatorInterface $translator,
        MedalChecker $medalChecker,
        IngressEventRepository $ingressEventRepository,
        string $pageBaseUrl,
        string $announceAdminCc
    ) {
        $this->emojiService = $emojiService;
        $this->translator = $translator;
        $this->medalChecker = $medalChecker;
        $this->pageBaseUrl = $pageBaseUrl;
        $this->announceAdminCc = $announceAdminCc;
        $this->ingressEventRepository = $ingressEventRepository;
    }

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

    protected function getAgentUserData(Agent $agent, User $user): array
    {
        $message = [];
        $message[] = 'Agent: '.$agent->getNickname();
        $message[] = 'ID: '.$agent->getId();
        $message[] = '';
        $message[] = 'User: '.$user->getUsername();
        $message[] = 'ID: '.$user->getId();
        $message[] = '';
        $message[] = 'Please verify!';

        return $message;
    }
}
