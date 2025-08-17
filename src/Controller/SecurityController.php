<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(
        AuthenticationUtils $authenticationUtils,
        Request $request,
        #[Autowire('%env(OAUTH_GOOGLE_ID)%')] string $oauthGoogleId,
        ?UserInterface $user = null,
    ): Response {
        if ($user instanceof \Symfony\Component\Security\Core\User\UserInterface) {
            // User is already logged in
            return $this->redirectToRoute('default');
        }

        $referer = $request->headers->get('referer');
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'security/login.html.twig',
            [
                'last_username' => $lastUsername,
                'error'         => $error,
                'referer'       => $referer,
                'oauthGoogleId' => $oauthGoogleId,
            ]
        );
    }
}
