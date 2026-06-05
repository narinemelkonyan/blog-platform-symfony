<?php

namespace App\Controller;

use App\Entity\Article;
use App\Handler\ArticleDraftHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/article/draft', name: 'app_article_draft_')]
class ArticleDraftController extends AbstractController
{
    public function __construct(
        private readonly ArticleDraftHandler $handler,
    ) {}

    /**
     * Saves a new article draft via AJAX.
     */
    #[Route('/new', name: 'new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $result =  $this->handler->new($request, $this->getUser());

        return new JsonResponse($result);
    }

    /**
     * Updates an existing article draft via AJAX.
     */
    #[Route('/{id}/update', name: 'update', methods: ['POST'])]
    public function update(Request $request, Article $article): JsonResponse
    {
        $result = $this->handler->update($request, $article, $this->getUser());

        return new JsonResponse($result);
    }
}
