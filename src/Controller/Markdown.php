<?php

namespace App\Controller;

use App\Service\MarkdownHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class Markdown extends AbstractController
{
    public function __construct(
        private readonly MarkdownHelper $markdownHelper
    ) {
    }

    /**
     * Converts a Markdown string to HTML.
     */
    #[Route(path: '/markdown/preview', name: 'markdown_preview', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    public function preview(
        Request $request
    ): JsonResponse {
        $text = (string)$request->request->get('text');

        return $this->json(
            [
                'data' => $text !== '' && $text !== '0'
                    ? $this->markdownHelper->parse($text)
                    : ':(',
            ]
        );
    }
}
