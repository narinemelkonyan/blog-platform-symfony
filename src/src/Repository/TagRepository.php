<?php

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function findBySlug(string $slug): ?Tag
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * Get popular tags
     */
    public function findPopular(int $limit = 10): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.articles', 'a')
            ->addSelect('COUNT(a.id) AS HIDDEN articleCount')
            ->groupBy('t.id')
            ->orderBy('articleCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get or create tag
     */
    public function findOrCreate(string $name, string $slug): Tag
    {
        $tag = $this->findOneBy(['slug' => $slug]);

        if (!$tag) {
            $tag = (new Tag())
                ->setName($name)
                ->setSlug($slug);

            $this->getEntityManager()->persist($tag);
        }

        return $tag;
    }
}
