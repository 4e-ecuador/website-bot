<?php

namespace App\EventListener;

use App\Entity\User;
use App\Service\TelegramBotHelper;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Security;

class UserChangedNotifier
{
    public function __construct(
        private readonly Security $security,
        private readonly TelegramBotHelper $telegramBotHelper,
        #[Autowire('%env(APP_ENV)%')] private readonly string $appEnv
    ) {
    }

    public function postUpdate(User $user, LifecycleEventArgs $event): void
    {
        if ('dev' === $this->appEnv) {
            return;
        }

        /**
         * @var User|null $adminUser
         */
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
            $adminUser->getUserAgentName()
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
