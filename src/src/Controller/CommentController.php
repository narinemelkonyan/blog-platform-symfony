<?php

namespace App\Controller;

use App\Entity\Article;
use App\Handler\CommentHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/article/{id}/comment', name: 'app_comment_')]
class CommentController extends AbstractController
{
    public function __construct(
        private readonly CommentHandler $handler
    ) {}

    #[Route('/new', name: 'new', methods: ['POST'])]
    public function new(Request $request, Article $article): Response
    {
        if ($request->request->get('phone')) {
            return new Response('', Response::HTTP_OK);
        }

        $result = $this->handler->new($request, $article, $this->getUser());

        if ($result->isRateLimitExceeded()) {
            $this->addFlash('error', $result->getRateLimitMessage());
            return $this->redirectToRoute('app_article_view', ['id' => $article->getId()]);
        }

        if ($result->isSuccess()) {
            return $this->redirectToRoute('app_article_view', ['id' => $article->getId()]);
        }

        if ($result->getForm() !== null) {
            foreach ($result->getForm()->getErrors(true) as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
        }

        return $this->redirectToRoute('app_article_view', ['id' => $article->getId()]);
    }

}
