<?php

namespace App\Handler;

use App\DTO\FormResult;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\User;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommentHandler
{
    public function __construct(
        #[Autowire(service: 'limiter.comment_limiter')]
        private readonly RateLimiterFactory $commentLimiter,
        private readonly EntityManagerInterface $em,
        private readonly FormFactoryInterface   $formFactory,
        private readonly UrlGeneratorInterface  $urlGenerator,
        private readonly CommentRepository      $commentRepository,
    ) {}

    /**
     * Builds comment form and handles submission.
     * Returns form on GET or validation error, RedirectResponse on success.
     */
    public function new(Request $request, Article $article, ?User $user): FormResult
    {
        $comment = new Comment();
        $form = $this->formFactory->create(CommentType::class, $comment, [
            'action' => $this->urlGenerator->generate('app_comment_new', ['id' => $article->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $limiter = $this->commentLimiter->create($request->getClientIp());
            if (!$limiter->consume(1)->isAccepted()) {
                return FormResult::rateLimitExceeded('Too many comments. Please wait before posting again.');
            }

            $comment->setArticle($article);
            $comment->setAuthor($user);

            $this->em->persist($comment);
            $this->em->flush();

            return FormResult::success();
        }

        return FormResult::failure($form);
    }

    /**
     * Returns threaded comments for article.
     */
    public function getThreadedComments(Article $article): array
    {
        return $this->commentRepository->findThreadedByArticle($article);
    }
}
