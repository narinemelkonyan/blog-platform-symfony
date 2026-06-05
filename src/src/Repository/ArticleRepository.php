<?php

namespace App\Repository;

use App\Entity\Article;
use App\Enum\ArticleStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    private const int ITEMS_PER_PAGE = 5;

    public function __construct(
        ManagerRegistry $registry,
        private readonly TagAwareCacheInterface $cachePopularArticles,
        private readonly TagAwareCacheInterface $cacheHeavyQueries,
    ) {
        parent::__construct($registry, Article::class);
    }

    /**
     * Returns paginated published articles ordered by creation date.
     */
    public function findPublished(int $page = 1, int $limit = self::ITEMS_PER_PAGE): array
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.status = :status')
            ->setParameter('status', ArticleStatus::PUBLISHED)
            ->orderBy('a.createdAt', 'DESC');

        return $this->paginate($qb, $page, $limit);
    }

    /**
     * Get popular articles — cached for 1 hour.
     */
    public function findPopular(int $limit = 5): array
    {
        return $this->cachePopularArticles->get(
            sprintf('popular_articles_%d', $limit),
            function (ItemInterface $item) use ($limit): array {
                $item->tag(['popular_articles']);

                return $this->createQueryBuilder('a')
                    ->addSelect('u')
                    ->join('a.author', 'u')
                    ->where('a.status = :status')
                    ->setParameter('status', ArticleStatus::PUBLISHED)
                    ->orderBy('a.createdAt', 'DESC')
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult();
            }
        );
    }

    /**
     * Get articles with authors and comment count
     */
    public function findArticlesWithAuthorsAndCommentCount(): array
    {
        return $this->cacheHeavyQueries->get(
            'articles_with_comment_count',
            function (ItemInterface $item): array {
                $item->tag(['articles_with_comment_count']);

                return $this->createQueryBuilder('a')
                    ->addSelect('u', 'COUNT(c.id) AS HIDDEN commentCount')
                    ->join('a.author', 'u')
                    ->leftJoin('a.comments', 'c')
                    ->where('a.status = :status')
                    ->setParameter('status', ArticleStatus::PUBLISHED)
                    ->groupBy('a.id')
                    ->orderBy('a.createdAt', 'DESC')
                    ->getQuery()
                    ->getResult();
            }
        );
    }

    /**
     * Get top authors by article count — cached for 30 minutes.
     */
    public function findTopAuthors(int $limit = 5): array
    {
        return $this->cacheHeavyQueries->get(
            sprintf('top_authors_%d', $limit),
            function (ItemInterface $item) use ($limit): array {
                $item->tag(['top_authors']);

                return $this->createQueryBuilder('a')
                    ->addSelect('u', 'COUNT(a.id) AS HIDDEN articleCount')
                    ->join('a.author', 'u')
                    ->where('a.status = :status')
                    ->setParameter('status', ArticleStatus::PUBLISHED)
                    ->groupBy('u.id')
                    ->orderBy('articleCount', 'DESC')
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult();
            }
        );
    }

    /**
     * Get all published articles with authors and tags.
     */
    public function findPublishedWithAuthorsAndTags(): array
    {
        return $this->createQueryBuilder('a')
            ->addSelect('u', 't')
            ->join('a.author', 'u')
            ->leftJoin('a.tags', 't')
            ->where('a.status = :status')
            ->setParameter('status', ArticleStatus::PUBLISHED)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns all slugs that match the base slug exactly or start with base followed by a hyphen.
     */
    public function findSlugsByBase(string $base): array
    {
        return $this->createQueryBuilder('a')
            ->select('a.slug')
            ->where('a.slug = :base OR a.slug LIKE :pattern')
            ->setParameter('base', $base)
            ->setParameter('pattern', $base . '-%')
            ->getQuery()
            ->getSingleColumnResult();
    }

    /**
     * Invalidates popular articles cache — call after article create/update/delete.
     */
    public function invalidatePopularCache(): void
    {
        $this->cachePopularArticles->invalidateTags(['popular_articles']);
    }

    /**
     * Invalidates heavy queries cache — call after article create/update/delete.
     */
    public function invalidateHeavyCache(): void
    {
        $this->cacheHeavyQueries->invalidateTags(['articles_with_comment_count', 'top_authors']);
    }

    /**
     * * Paginates a query result and returns pagination metadata alongside the items.
     */
    private function paginate(QueryBuilder $qb, int $page, int $limit): array
    {
        $query = $qb
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        $paginator = new Paginator($query);
        $total = $paginator->count();
        $pages = (int) ceil($total / $limit);

        return [
            'items' => iterator_to_array($paginator),
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'hasNext' => $page < $pages,
            'hasPrev' => $page > 1,
            'limit' => $limit,
        ];
    }
}
