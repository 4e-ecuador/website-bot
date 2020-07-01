<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class TwigListener
{
    private Environment $twig;
    private string $locale;

    public function __construct(
        Environment $twig,
        TranslatorInterface $translator
    ) {
        $this->twig = $twig;
        $this->locale = $translator->getLocale();
    }

    public function __invoke(ControllerEvent $event): void
    {
        $this->twig->addGlobal('locale', $this->locale);
    }
}
