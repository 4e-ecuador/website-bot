<?php

namespace App\EventSubscriber;

use App\Entity\LoginAttempt;
use App\Security\AppCustomAuthenticator;
use App\Security\GoogleAuthenticator;
use App\Security\GoogleIdentityAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginAttemptSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            LoginFailureEvent::class => 'onLoginFailure',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $loginAttempt = new LoginAttempt();
        $loginAttempt->setSuccess(true);
        $loginAttempt->setEmail($event->getUser()->getUserIdentifier());
        $loginAttempt->setIpAddress($event->getRequest()->getClientIp());
        $loginAttempt->setAuthMethod($this->resolveAuthMethod($event->getAuthenticator()));

        $this->entityManager->persist($loginAttempt);
        $this->entityManager->flush();
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $email = null;
        $passport = $event->getPassport();

        if ($passport instanceof Passport) {
            $email = $passport->getUser()->getUserIdentifier();
        }

        if (!$email) {
            $requestEmail = $event->getRequest()->request->get('email')
                ?? $event->getRequest()->request->get('_username');
            $email = is_string($requestEmail) ? $requestEmail : null;
        }

        $loginAttempt = new LoginAttempt();
        $loginAttempt->setSuccess(false);
        $loginAttempt->setEmail($email);
        $loginAttempt->setIpAddress($event->getRequest()->getClientIp());
        $loginAttempt->setAuthMethod($this->resolveAuthMethod($event->getAuthenticator()));

        $this->entityManager->persist($loginAttempt);
        $this->entityManager->flush();
    }

    private function resolveAuthMethod(AuthenticatorInterface $authenticator): string
    {
        return match ($authenticator::class) {
            GoogleAuthenticator::class => 'google',
            GoogleIdentityAuthenticator::class => 'google_identity',
            AppCustomAuthenticator::class => 'form',
            default => 'unknown',
        };
    }
}
