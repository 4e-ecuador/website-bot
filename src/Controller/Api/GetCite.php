<?php

namespace App\Controller\Api;

use App\Service\CiteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GetCite extends AbstractController
{
    public function __construct(private CiteService $citeService)
    {
    }

    public function __invoke(): string
    {
        return $this->citeService->getRandomCite();
    }
}
