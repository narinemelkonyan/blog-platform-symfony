<?php

namespace App\Builder;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Builds a password reset request email.
 */
class PasswordResetRequestBuilder extends AbstractEmailBuilder
{
    /**
     * Builds a password reset email for the given user.
     */
    public function build(User $user, string $token): ?TemplatedEmail
    {
        $resetUrl = $this->urlGenerator->generate(
            'app_reset_password',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return (new TemplatedEmail())
            ->from($this->mailerFrom)
            ->to($user->getEmail())
            ->subject('Password Reset Request')
            ->htmlTemplate('email/password-reset-request.html.twig')
            ->context([
                'user_email' => $user->getEmail(),
                'reset_url' => $resetUrl,
            ]);
    }
}
