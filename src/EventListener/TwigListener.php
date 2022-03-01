<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class TwigListener
{
    private readonly string $locale;

    public function __construct(
        private readonly Environment $twig,
        TranslatorInterface $translator
    ) {
        $this->locale = $translator->getLocale();
    }

    public function __invoke(ControllerEvent $event): void
    {
        $this->twig->addGlobal('locale', $this->locale);
    }
}
