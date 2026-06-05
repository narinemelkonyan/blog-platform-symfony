<?php

namespace App\Security\Voter;

use App\Entity\Article;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ArticleVoter extends Voter
{
    public const EDIT   = 'ARTICLE_EDIT';
    public const DELETE = 'ARTICLE_DELETE';
    public const VIEW   = 'ARTICLE_VIEW';

    /**
     * Determines if this voter can vote on the given attribute and subject.
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE, self::VIEW])
            && $subject instanceof Article;
    }

    /**
     * Performs the access control logic.
     *
     * - ARTICLE_VIEW   — everyone can view
     * - ARTICLE_EDIT   — author or admin
     * - ARTICLE_DELETE — author or admin
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, Vote|null $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return $attribute === self::VIEW;
        }

        /** @var Article $article */
        $article = $subject;

        return match ($attribute) {
            self::VIEW   => true,
            self::EDIT   => $this->canEdit($article, $user),
            self::DELETE => $this->canDelete($article, $user),
            default      => false,
        };
    }

    /**
     * Author or admin can edit the article.
     */
    private function canEdit(Article $article, User $user): bool
    {
        return $article->isAuthor($user)
            || in_array(User::ROLE_ADMIN, $user->getRoles(), true);
    }

    /**
     * Author or admin can delete the article.
     */
    private function canDelete(Article $article, User $user): bool
    {
        return $article->isAuthor($user)
            || in_array(User::ROLE_ADMIN, $user->getRoles(), true);
    }
}
