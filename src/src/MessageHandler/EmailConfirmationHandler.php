<?php

namespace App\MessageHandler;

use App\Builder\EmailConfirmationBuilder;
use App\Entity\User;
use App\Message\EmailConfirmationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class EmailConfirmationHandler
{
    public function __construct(
        private readonly MailerInterface          $mailer,
        private readonly EmailConfirmationBuilder $builder,
        private readonly LoggerInterface          $logger,
        private EntityManagerInterface $em,
    ) {}

    /**
     * Processes the email confirmation message.
     */
    public function __invoke(EmailConfirmationMessage $message): void
    {
        $user = $this->em->getRepository(User::class)->find($message->getUserId());

        if (!$user instanceof User) {
            $this->logger->error('EmailConfirmationHandler: user not found', [
                'userId' => $message->getUserId(),
            ]);
            return;
        }

        $email = $this->builder->build(
            $user,
            $message->getToken(),
        );

        $this->mailer->send($email);

        $this->logger->info('Confirmation email sent', [
            'userId' => $message->getUserId(),
        ]);
    }
}
