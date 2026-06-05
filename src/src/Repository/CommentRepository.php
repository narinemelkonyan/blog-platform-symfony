<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * Get root comments with replies and authors for article in a single query
     */
    public function findThreadedByArticle(Article $article): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.replies', 'replies')
            ->leftJoin('c.author', 'author')
            ->addSelect('replies', 'author')
            ->where('c.article = :article')
            ->andWhere('c.parent IS NULL')
            ->andWhere('c.isApproved = true')
            ->setParameter('article', $article)
            ->orderBy('c.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
