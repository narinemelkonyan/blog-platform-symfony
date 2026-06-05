<?php

namespace App\Handler;

use App\DTO\FormResult;
use App\Entity\Article;
use App\Entity\User;
use App\Enum\ArticleStatus;
use App\Form\ArticleType;
use App\Message\ArticleViewMessage;
use App\Repository\ArticleRepository;
use App\Service\ImageProcessorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

class ArticleHandler
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly FormFactoryInterface $formFactory,
        private readonly ArticleRepository $articleRepository,
        private readonly ImageProcessorService $imageProcessor,
        private readonly CommentHandler $commentHandler,
        private readonly HtmlSanitizerInterface $htmlSanitizer,
        private readonly MessageBusInterface $bus,
        private readonly RequestStack $requestStack,
        private readonly string $uploadsDir,
    ) {}

    /**
     * Handles the article list display.
     */
    public function index(int $page = 1): array
    {
        return $this->articleRepository->findPublished($this->normalizePage($page));
    }

    /**
     * Handles the article creation form submission.
     */
    public function create(Request $request, User $user): FormResult
    {
        $article = new Article();
        $form = $this->formFactory->create(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverFile = $form->get('coverImage')->getData();
            $filename = $this->imageProcessor->processArticleCover($coverFile, $this->uploadsDir);
            $article->setContent($this->htmlSanitizer->sanitize(html_entity_decode($article->getContent())));
            $article->setStatus($request->request->getEnum('action', ArticleStatus::class, ArticleStatus::DRAFT));
            $article->setCoverImage($filename);
            $article->setAuthor($user);
            $this->em->persist($article);
            $this->em->flush();

            $this->articleRepository->invalidatePopularCache();
            $this->articleRepository->invalidateHeavyCache();

            return FormResult::success($article);
        }

        return FormResult::failure($form);
    }

    /**
     * Handles the article update form submission.
     */
    public function update(Request $request, Article $article): FormResult
    {
        $form = $this->formFactory->create(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverFile = $form->get('coverImage')->getData();

            $oldCoverFileName = null;
            if ($coverFile !== null) {
                $oldCoverFileName = $article->getCoverImage();
                $filename = $this->imageProcessor->processArticleCover($coverFile, $this->uploadsDir);
                $article->setCoverImage($filename);
            }
            $article->setContent($this->htmlSanitizer->sanitize(html_entity_decode($article->getContent())));
            $article->setStatus($request->request->getEnum('action', ArticleStatus::class, ArticleStatus::DRAFT));
            $this->em->flush();

            if ($oldCoverFileName !== null) {
                $this->imageProcessor->deleteFile($oldCoverFileName, $this->uploadsDir);
            }

            $this->articleRepository->invalidatePopularCache();
            $this->articleRepository->invalidateHeavyCache();

            return FormResult::success($article);
        }

        return FormResult::failure($form);
    }

    /**
     * Checks whether the current user has access to view the article.
     */
    public function getArticleForView(Article $article, ?User $user): Article
    {
        if ($article->getStatus() === ArticleStatus::DRAFT) {
            $isAuthor = $user !== null && $article->isAuthor($user);
            $isAdmin = $user !== null && $user->hasRole(User::ROLE_ADMIN);

            if (!$isAuthor && !$isAdmin) {
                throw new AccessDeniedHttpException('Access denied');
            }
        }

        if ($article->getStatus() === ArticleStatus::PUBLISHED) {
            $sessionKey = 'viewed_article_' . $article->getId();
            $session = $this->requestStack->getSession();
            if (!$session->has($sessionKey)) {
                $session->set($sessionKey, true);
                $this->bus->dispatch(new ArticleViewMessage($article->getId()));
            }

        }

        return $article;
    }

    /**
     * Returns the comment form for the given article, or null if the user is not authenticated.
     */
    public function getCommentsForm(Article $article, Request $request, ?User $user): ?FormInterface
    {
        $commentForm = null;

        if ($user !== null) {
            $commentForm = $this->commentHandler->new($request, $article, $user);
            $commentForm = $commentForm->getForm();
        }

        return $commentForm;
    }

    /**
     * Returns threaded comments for the given article.
     */
    public function getArticleComments(Article $article): array
    {
        return $this->commentHandler->getThreadedComments($article);
    }
    /**
     * Handles the article deletion.
     */
    public function delete(Article $article): void
    {
        if ($article->getCoverImage() !== null) {
            $this->imageProcessor->deleteFile($article->getCoverImage(), $this->uploadsDir);
        }
       $this->em->remove($article);
       $this->em->flush();

        $this->articleRepository->invalidatePopularCache();
        $this->articleRepository->invalidateHeavyCache();
    }

    /**
     * Normalizes the page number to be within the valid range [1, 1000].
     */
    private function normalizePage(int $page): int
    {
        return min(1000, max(1, $page));
    }

}
