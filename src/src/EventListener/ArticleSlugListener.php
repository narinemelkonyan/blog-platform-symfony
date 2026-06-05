<?php

namespace App\EventListener;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEntityListener(event: Events::prePersist, entity: Article::class)]
class ArticleSlugListener
{
    public function __construct(
        private readonly SluggerInterface  $slugger,
        private readonly ArticleRepository $articleRepository,
    ) {}

    /**
     * Generate slug before insert
     */
    public function prePersist(Article $article): void
    {
        $this->generateSlug($article);
    }

    /**
     * Generates a unique slug from the article title.
     */
    private function generateSlug(Article $article): void
    {
        if (!empty($article->getSlug())) {
            return;
        }

        $base   = $this->slugger->slug($article->getTitle())->lower()->toString();
        $slug   = $base;
        $suffix = 1;
        $articles = $this->articleRepository->findSlugsByBase($base);

        while (in_array($slug, $articles, true)) {
            $slug = $base . '-' . $suffix++;
        }

        $article->setSlug($slug);
    }
}
