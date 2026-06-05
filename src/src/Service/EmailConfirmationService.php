<?php

namespace App\Service;

use App\Entity\User;
use App\Exception\InvalidTokenException;
use App\Exception\TokenExpiredException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Handles email confirmation logic.
 */
class EmailConfirmationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    /**
     * Confirms the user's email address by validating the token.
     */
    public function confirm(string $token)
    {
        $user = $this->getUserByToken($token);
        if (!$user) {
            throw new InvalidTokenException('Invalid confirmation token.');
        }

        $emailConfirmationSentTime = $user->getEmailConfirmationSentTime();
        if (!$emailConfirmationSentTime || $this->isTokenExpired($emailConfirmationSentTime, '24 hours')) {
            throw new TokenExpiredException('Token expired. Please register again.');
        }

        if ($user->getPendingEmail() !== null) {
            $user->setEmail($user->getPendingEmail());
            $user->setPendingEmail(null);
        }

        $user->setEmailConfirmed(true);
        $user->setConfirmationCode(null);
        $this->em->flush();
    }

    /**
     *  Finds a user by their hashed confirmation token.
     * /
     */
    private function getUserByToken(string $token): ?User
    {
        $hashed = hash('sha256', $token);

        return $this->em->getRepository(User::class)->findOneBy([
            'confirmationCode' => $hashed,
        ]);
    }

    /**
     * Checks whether the token has expired.
     */
    private function isTokenExpired(\DateTimeImmutable $sentTime, string $ttl): bool
    {
        return $sentTime < new \DateTimeImmutable('-' . $ttl);
    }
}
