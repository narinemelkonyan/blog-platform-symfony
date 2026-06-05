<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Like;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Like::class);
    }

    /**
     * Find like by user and article
     */
    public function findByUserAndArticle(User $user, Article $article): ?Like
    {
        return $this->findOneBy([
            'user'    => $user,
            'article' => $article,
        ]);
    }

    /**
     * Get count of likes by article
     */
    public function countByArticle(Article $article): int
    {
        return $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.article = :article')
            ->setParameter('article', $article)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
