<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GoogleController extends AbstractController
{
    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/connect/google", name="connect_google_start")
     */
    public function connectAction(ClientRegistry $clientRegistry): RedirectResponse
    {
        // $this->get('session')->set('referer', $request->headers->get('referer'));

        return $clientRegistry
            ->getClient('google')
            ->redirect(
                [
                    'profile',
                    'email' // the scopes you want to access
                ]
            );
    }

    /**
     * After going to Google, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml
     *
     * @Route("/connect/google/check", name="connect_google_check")
     */
    public function connectCheckAction(
        Request $request,
        ClientRegistry $clientRegistry
    ): RedirectResponse {
        // return $this->redirectToRoute('default');
        $referer = $this->get('session')->get('referer');
        if (null === $referer)
        {
            // return new RedirectResponse($this->generateUrl('home'));
            return $this->redirectToRoute('default');
        }
        return new RedirectResponse($referer);
    }
}
