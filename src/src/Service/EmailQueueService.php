<?php

namespace App\Service;

use App\Entity\User;
use App\Message\EmailConfirmationMessage;
use App\Message\PasswordResetRequestMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EmailQueueService
{
    public function __construct(
        private MessageBusInterface $bus,
        private EntityManagerInterface $em,
    ) {}

    /**
     * Queues an email confirmation message for the given user.
     */
    public function sendEmailConfirmation(User $user): void
    {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);

        $user->setConfirmationCode($hashedToken);
        $user->setEmailConfirmationSentTime(new \DateTimeImmutable());

        $this->em->flush();

        $this->bus->dispatch(new EmailConfirmationMessage(
            $user->getId(),
            $token,
        ));
    }

    /**
     * Queues a password reset email for the given email address.
     */
    public function sendEmailResetPassword(string $email): void
    {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            return;
        }

        if (!$user->isEmailConfirmed()) {
            return;
        }

        $user->setPasswordResetCode($hashedToken);
        $user->setPasswordResetSentTime(new \DateTimeImmutable());
        $this->em->flush();

        $this->bus->dispatch(new PasswordResetRequestMessage(
            $user->getId(),
            $token,
        ));
    }
}
