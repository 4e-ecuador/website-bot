<?php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

class LocaleListener
{
    public function __construct(
        private readonly Security $security,
        /**
         * @var Translator $translator
         */
        private readonly TranslatorInterface $translator
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        /** @var User|null $user */
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
