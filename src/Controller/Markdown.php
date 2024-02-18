<?php

namespace App\Controller;

use App\Service\MarkdownHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/markdown')]
class Markdown extends AbstractController
{
    /**
     * Converts a markdown string to HTML.
     */
    #[Route(path: '/preview', name: 'markdown_preview', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    public function preview(
        Request $request,
        MarkdownHelper $markdownHelper
    ): JsonResponse {
        $text = (string)$request->request->get('text');

        return $this->json(
            ['data' => $text !== '' && $text !== '0' ? $markdownHelper->parse($text) : ':(']
        );
    }
}
