<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @method User getUser()
 */
class BaseController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    protected function getDoctrine(): ManagerRegistry
    {
        return $this->entityManager;
    }
}
