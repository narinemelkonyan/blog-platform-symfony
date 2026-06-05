<?php

namespace App\Builder;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Builds an email confirmation message.
 */
class EmailConfirmationBuilder extends AbstractEmailBuilder
{
    /**
     * Builds an email confirmation message for the given user.
     */
    public function build(User $user, string $token): ?TemplatedEmail
    {
        $confirmationUrl = $this->urlGenerator->generate(
            'app_email_confirm',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return (new TemplatedEmail())
            ->from($this->mailerFrom)
            ->to($user->getPendingEmail() ?? $user->getEmail())
            ->subject('Confirm your email')
            ->htmlTemplate('email/confirmation.html.twig')
            ->context([
                'user_email'  => $user->getPendingEmail() ?? $user->getEmail(),
                'confirmation_url' => $confirmationUrl,
            ]);
    }
}
