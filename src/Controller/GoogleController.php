<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;

class GoogleController extends AbstractController
{
    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly RequestStack $requestStack
    ) {
    }

    /**
     * Link to this controller to start the "connect" process
     */
    #[Route(path: '/connect/google', name: 'connect_google_start')]
    public function connect(): RedirectResponse
    {
        return $this->clientRegistry
            ->getClient('google')
            ->redirect(
                [
                    'profile',
                    'email', // the scopes you want to access
                ],
                []
            );
    }

    /**
     * After going to Google, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml
     */
    #[Route(path: '/connect/google/check', name: 'connect_google_check')]
    public function connectCheck(): RedirectResponse
    {
        $referer = $this->requestStack->getSession()->get('referrer');
        if (null === $referer) {
            return $this->redirectToRoute('default');
        }

        return new RedirectResponse($referer);
    }
}
