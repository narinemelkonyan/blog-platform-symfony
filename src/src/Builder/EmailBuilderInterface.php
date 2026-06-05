<?php

namespace App\Builder;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

/**
 * Interface for email builders.
 */
interface EmailBuilderInterface
{
    /**
     * Builds a templated email for the given user and token.
     */
    public function build(User $user, string $token): ?TemplatedEmail;

}
