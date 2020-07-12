<?php

namespace App\Controller\Api;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GetMeAction extends AbstractController
{
    public function __invoke(): User
    {
        /** @var User $user */
        $user = $this->getUser();

        return $user;
    }
}
