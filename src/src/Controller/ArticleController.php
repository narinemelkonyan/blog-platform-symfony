<?php

namespace App\Controller;

use App\Entity\Article;
use App\Handler\ArticleHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/article')]
class ArticleController extends AbstractController
{
    public function __construct(
        private readonly ArticleHandler $handler
    ) {}

    /**
     * Renders the paginated article list page.
     */
    #[Route('/', name: 'app_article_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $pagination = $this->handler->index($request->query->getInt('page'));

        return $this->render('article/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Renders the article creation page and processes the form submission.
     */
    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $result = $this->handler->create($request, $this->getUser());

        if ($result->isSuccess()) {
            return $this->redirectToRoute('app_article_view', ['id' => $result->getData()->getId()]);
        }

        return $this->render('article/new.html.twig', ['form' => $result->getForm()]);
    }

    /**
     * Renders the article detail page.
     */
    #[Route('/{id}', name: 'app_article_view', methods: ['GET'])]
    public function view(Article $article, Request $request): Response
    {
        return $this->render('article/view.html.twig',
            [
            'article' => $this->handler->getArticleForView($article, $this->getUser()),
            'comments'    => $this->handler->getArticleComments($article, ),
            'commentForm' => $this->handler->getCommentsForm($article, $request, $this->getUser()),
            ]
        );
    }

    /**
     * Renders the article edit page and processes the form submission.
     */
    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article): Response
    {
        $result = $this->handler->update($request, $article);

        if ($result->isSuccess()) {
            return $this->redirectToRoute('app_article_view', ['id' => $result->getData()->getId()]);
        }

        return $this->render('article/edit.html.twig', ['form' => $result->getForm(), 'article' => $article]);
    }

    /**
     * Processes the article deletion request.
     */
    #[Route('/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }

        $this->handler->delete($article);

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }
}
