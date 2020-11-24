<?php

namespace App\EventListener;

use App\Entity\User;
use App\Service\TelegramBotHelper;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Security;

class UserChangedNotifier
{
    private Security $security;
    private TelegramBotHelper $telegramBotHelper;
    private string $appEnv;

    public function __construct(
        Security $security,
        TelegramBotHelper $telegramBotHelper,
        string $appEnv
    ) {
        $this->security = $security;
        $this->telegramBotHelper = $telegramBotHelper;
        $this->appEnv = $appEnv;
    }

    public function postUpdate(User $user, LifecycleEventArgs $event): void
    {
        if ('dev' === $this->appEnv) {
            return;
        }

        $adminUser = $this->security->getUser();

        if (!$adminUser) {
            // Change has not been performed by an admin - but during a login...
            return;
        }

        if ($user === $adminUser) {
            // Change has been performed by the user - no message required (?)
            return;
        }

        $groupId = $this->telegramBotHelper->getGroupId('admin');

        $text = [];

        $text[] = '*User account changed.*';
        $text[] = '';
        $text[] = sprintf(
            'A user account has been changed by %s'
            ,
            $adminUser->getUsername()
        );
        $text[] = '';
        $text[] = 'ID: '.$user->getId();
        $text[] = 'Email: '.$user->getEmail();
        $text[] = 'Username: '.$user->getUserAgentName();
        $text[] = 'Agent: '.($user->getAgent() ? $user->getAgent()
                ->getNickname() : '');
        $text[] = str_replace(
            '_',
            '\\_',
            'Roles: '.implode(', ', $user->getRoles())
        );

        $this->telegramBotHelper->sendMessage($groupId, implode("\n", $text));
    }
}
