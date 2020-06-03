<?php

namespace App\EventListener;

use App\Entity\User;
use App\Service\TelegramBotHelper;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Security;

class UserChangedNotifier
{
    /**
     * @var Security
     */
    private $security;

    /**
     * @var TelegramBotHelper
     */
    private $telegramBotHelper;

    private $appEnv;

    public function __construct(Security $security, TelegramBotHelper $telegramBotHelper, string $appEnv)
    {
        $this->security = $security;
        $this->telegramBotHelper = $telegramBotHelper;
        $this->appEnv = $appEnv;
    }

    public function postUpdate(User $user, LifecycleEventArgs $event): void
    {
        if ('dev' === $this->appEnv) {
            return;
        }

        $groupId = $_ENV['ANNOUNCE_GROUP_ID_ADMIN'];

        $adminUser = $this->security->getUser();

        $text = [];

        $text[] = '*User account changed.*';
        $text[] = '';
        $text[] = sprintf(
            'A user account has been changed by %s'
            , $adminUser->getUsername()
        );
        $text[] = '';
        $text[] = 'ID: '.$user->getId();
        $text[] = 'Email: '.$user->getEmail();
        $text[] = 'Username: '.$user->getUsername();
        $text[] = 'Agent: '.($user->getAgent() ? $user->getAgent()
                ->getNickname() : '');
        $text[] = str_replace(
            '_', '\\_', 'Roles: '.implode(', ', $user->getRoles())
        );

        $this->telegramBotHelper->sendMessage($groupId, implode("\n", $text));
    }
}
