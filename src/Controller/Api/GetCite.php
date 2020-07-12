<?php

namespace App\Controller\Api;

use App\Service\CiteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GetCite extends AbstractController
{
    private CiteService $citeService;

    public function __construct(CiteService $citeService)
    {
        $this->citeService = $citeService;
    }

    public function __invoke(): string
    {
        return $this->citeService->getRandomCite();
    }
}
