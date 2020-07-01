<?php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class LocaleListener
{
    private Security $security;
    private TranslatorInterface $translator;

    public function __construct(
        Security $security,
        TranslatorInterface $translator
    ) {
        $this->security = $security;
        $this->translator = $translator;
    }

    public function __invoke(RequestEvent $event): void
    {
        /* @type User $user */
        $user = $this->security->getUser();

        if (!$user) {
            return;
        }

        $agent = $user->getAgent();

        if (!$agent) {
            return;
        }

        // @todo dummy init object... why??
        $agent->getNickname();

        $locale = $agent->getLocale();

        if ($locale) {
            // $request = $event->getRequest();
            // $request->setLocale($locale);
            $this->translator->setLocale($locale);
        }
    }
}
