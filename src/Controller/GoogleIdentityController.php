<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class GoogleIdentityController extends AbstractController
{
    /**
     * This route is needed for the authenticator.
     */
    #[Route(path: '/connect/google/verify', name: 'connect_google_verify', methods: ['POST'])]
    public function connectVerify(): void
    {
    }
}
