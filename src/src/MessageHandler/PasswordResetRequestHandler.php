<?php

namespace App\MessageHandler;

use App\Builder\PasswordResetRequestBuilder;
use App\Entity\User;
use App\Message\PasswordResetRequestMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PasswordResetRequestHandler
{
    public function __construct(
        private readonly MailerInterface          $mailer,
        private readonly PasswordResetRequestBuilder $builder,
        private readonly LoggerInterface          $logger,
        private EntityManagerInterface $em,
    ) {}

    /**
     * Processes the password reset request message.
     */
    public function __invoke(PasswordResetRequestMessage $message): void
    {
        $user = $this->em->getRepository(User::class)->find($message->getUserId());

        if (!$user instanceof User) {
            $this->logger->error('PasswordResetRequestHandler: user not found', [
                'userId' => $message->getUserId(),
            ]);
            return;
        }

        $email = $this->builder->build(
            $user,
            $message->getToken(),
        );

        $this->mailer->send($email);

        $this->logger->info('Password Reset Request email sent', [
            'userId' => $message->getUserId(),
        ]);
    }
}
