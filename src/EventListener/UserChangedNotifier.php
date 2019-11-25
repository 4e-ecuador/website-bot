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

    public function __construct(Security $security, TelegramBotHelper $telegramBotHelper)
    {
        $this->security = $security;
        $this->telegramBotHelper = $telegramBotHelper;
    }

    // the entity listener methods receive two arguments:
    // the entity instance and the lifecycle event
    public function postUpdate(User $user, LifecycleEventArgs $event): void
    {
        // $groupId = $_ENV['ANNOUNCE_GROUP_ID_TEST'];
        $groupId = $_ENV['ANNOUNCE_GROUP_ID_ADMIN'];

        $adminUser = $this->security->getUser();

        $text = [];

        $text[] = '*User account changed.*';
        $text[] = '';
        $text[] = sprintf(
            'A user account has been changed by %s'
            // , $user->getUsername(), $user->getId()
            , $adminUser->getUsername()
        );
        $text[] = '';
        $text[] = 'ID: '.$user->getId();
        $text[] = 'Email: '.$user->getEmail();
        $text[] = 'Username: '.$user->getUsername();
        $text[] = 'Agent: '.($user->getAgent() ? $user->getAgent()
                ->getNickname() : '');
        $text[] = 'Roles: '.implode(', ', $user->getRoles());

        $this->telegramBotHelper->sendMessage($groupId, implode("\n", $text));
    }
}
