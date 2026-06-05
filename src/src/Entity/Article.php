<?php

namespace App\Entity;

use App\Enum\ArticleStatus;
use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'article')]
#[ORM\Index(columns: ['slug'], name: 'idx_article_slug')]
#[ORM\Index(columns: ['status', 'created_at'], name: 'idx_article_status_created_at')]
#[UniqueEntity(fields: ['slug'])]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $author = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Title is required.')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Title must be at least {{ limit }} characters.',
        maxMessage: 'Title cannot exceed {{ limit }} characters.',
    )]
    private ?string $title = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\Length(max: 255, maxMessage: 'Slug cannot exceed {{ limit }} characters.')]
    #[Assert\Regex(
        pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
        message: 'Slug can only contain lowercase letters, numbers and hyphens.',
    )]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 10)]
    private ?string $content = null;

    #[ORM\Column(type: 'string', enumType: ArticleStatus::class, options: ['default' => 'draft'])]
    private ArticleStatus $status = ArticleStatus::DRAFT;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'articles')]
    #[ORM\JoinTable(name: 'article_tags')]
    private Collection $tags;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Comment::class, cascade: ['remove'])]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Like::class, cascade: ['remove'])]
    private Collection $likes;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coverImage = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $viewCount = 0;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Category $category = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->tags = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->likes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(User $author): static
    {
        $this->author = $author;
        return $this;
    }

    public function isAuthor(User $user): bool
    {
        return $this->author->getId() === $user->getId();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getStatus(): ArticleStatus
    {
        return $this->status;
    }

    public function setStatus(ArticleStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function isPublished(): bool
    {
        return $this->status === ArticleStatus::PUBLISHED;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->getArticles()->add($this);
        }
        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            $tag->getArticles()->removeElement($this);
        }
        return $this;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function getLikesCount(): int
    {
        return $this->likes->count();
    }

    public function getViewCount(): int
    {
        return $this->viewCount;
    }

    public function setViewCount($viewCount): static
    {
        $this->viewCount = $viewCount;
        return $this;
    }

    public function getCoverImage(): ?string
    {
      return $this->coverImage;
    }

    public function setCoverImage(?string $coverImage): static
    {
        $this->coverImage = $coverImage;
        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        return $this;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
