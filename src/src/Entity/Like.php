<?php

namespace App\Entity;

use App\Repository\LikeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LikeRepository::class)]
#[ORM\Table(name: 'likes')]
#[ORM\UniqueConstraint(name: 'unique_user_article_like', columns: ['user_id', 'article_id'])]
class Like
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'likes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Article $article;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getUser(): User { return $this->user; }
    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getArticle(): Article { return $this->article; }
    public function setArticle(Article $article): static
    {
        $this->article = $article;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
