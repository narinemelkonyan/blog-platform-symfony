<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Get root categories with their children in a single query
     */
    public function findRootCategoriesWithChildren(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.children', 'children')
            ->addSelect('children')
            ->where('c.parent IS NULL')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * All categories tree.
     */
    public function findTree(): array
    {
        $all = $this->createQueryBuilder('c')
            ->leftJoin('c.parent', 'parent')
            ->addSelect('parent')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();

        return array_filter($all, fn(Category $c) => $c->isRoot());
    }

    /**
     * Get category by slug.
     */
    public function findBySlug(string $slug): ?Category
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
