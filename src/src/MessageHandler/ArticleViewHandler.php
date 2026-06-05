<?php

namespace App\MessageHandler;

use App\Message\ArticleViewMessage;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ArticleViewHandler
{
    public function __construct(
        private readonly ArticleRepository      $articleRepository,
        private readonly EntityManagerInterface $em,
    ) {}

    public function __invoke(ArticleViewMessage $message): void
    {
        $article = $this->articleRepository->find($message->articleId);

        if ($article === null) {
            return;
        }
        $article->setViewCount($article->getViewCount() + 1);
        $this->em->persist($article);
        $this->em->flush();

        $this->articleRepository->invalidatePopularCache();
        $this->articleRepository->invalidateHeavyCache();
    }
}
