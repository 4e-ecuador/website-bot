<?php

namespace App\Controller\Api;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * bklabkia GetMeAction jhjui
 */
class GetMeAction extends AbstractController
{
    /**
     * hakljhlkajhlakj haiuhaiuh
     */
    public function __invoke(): User
    {
        /** @var User $user */
        $user = $this->getUser();

        return $user;
    }
}
