<?php

namespace App\Controller;

use App\Helper\Paginator\PaginatorTrait;
use App\Repository\LoginAttemptRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function count;

class LoginAttemptController extends BaseController
{
    use PaginatorTrait;

    public function __construct(
        private readonly LoginAttemptRepository $loginAttemptRepository,
    ) {
    }

    #[Route(path: '/login-attempt/', name: 'login_attempt_index', methods: [
        'GET',
        'POST',
    ])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(
        Request $request
    ): Response {
        $paginatorOptions = $this->getPaginatorOptions($request);
        $attempts = $this->loginAttemptRepository->getPaginatedList(
            $paginatorOptions
        );
        $paginatorOptions->setMaxPages(
            (int)ceil(count($attempts) / $paginatorOptions->getLimit())
        );

        return $this->render(
            'login_attempt/index.html.twig',
            [
                'login_attempts' => $attempts,
                'paginatorOptions' => $paginatorOptions,
            ]
        );
    }
}
