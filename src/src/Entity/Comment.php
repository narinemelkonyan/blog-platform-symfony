<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\Table(name: 'comments')]
#[ORM\Index(columns: ['article_id', 'parent_id', 'is_approved'], name: 'idx_comment_article_parent_approved')]
#[ORM\Index(columns: ['created_at'], name: 'idx_comment_created_at')]
#[ORM\Index(columns: ['author_id'], name: 'idx_comment_author')]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Article $article;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $author;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'replies')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Comment $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $replies;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(options: ['default' => false])]
    private bool $isApproved = false;

    public function __construct()
    {
        $this->replies = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getContent(): string { return $this->content; }
    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getArticle(): Article { return $this->article; }
    public function setArticle(Article $article): static
    {
        $this->article = $article;
        return $this;
    }

    public function getAuthor(): User { return $this->author; }
    public function setAuthor(User $user): static
    {
        $this->author = $user;
        return $this;
    }

    public function getParent(): ?self { return $this->parent; }
    public function setParent(?self $parent): static
    {
        $this->parent = $parent;
        return $this;
    }

    /** @return Collection<int, Comment> */
    public function getReplies(): Collection { return $this->replies; }

    public function addReply(Comment $reply): static
    {
        if (!$this->replies->contains($reply)) {
            $this->replies->add($reply);
            $reply->setParent($this);
        }
        return $this;
    }

    public function removeReply(Comment $reply): static
    {
        if ($this->replies->removeElement($reply)) {
            if ($reply->getParent() === $this) {
                $reply->setParent(null);
            }
        }
        return $this;
    }

    public function isRoot(): bool { return $this->parent === null; }
    public function hasReplies(): bool { return !$this->replies->isEmpty(); }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function isApproved(): bool { return $this->isApproved; }
    public function setIsApproved(bool $isApproved): static
    {
        $this->isApproved = $isApproved;
        return $this;
    }
}
