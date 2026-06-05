<?php

namespace App\Service;

use App\DTO\FormResult;
use App\Entity\User;
use App\Exception\InvalidTokenException;
use App\Exception\TokenExpiredException;
use App\Form\PasswordResetRequestType;
use App\Form\PasswordResetType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/**
 *  Handles password reset request and password reset logic.
 * /
 */
class PasswordResetService
{
    public function __construct(
        #[Autowire(service: 'limiter.password_reset_limiter')]
        private readonly RateLimiterFactory $passwordResetLimiter,
        private readonly EntityManagerInterface $em,
        private readonly FormFactoryInterface $formFactory,
        private readonly EmailQueueService $emailQueueService,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    /**
     * Handles the password reset request form.
     * Sends a reset email if the form is valid.
     */
    public function reset(Request $request): FormResult
    {
        $limiter = $this->passwordResetLimiter->create($request->getClientIp());
        if (!$limiter->consume(1)->isAccepted()) {
            return FormResult::rateLimitExceeded('Too many reset attempts. Please try again later.');
        }

        $form = $this->formFactory->create(PasswordResetRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->emailQueueService->sendEmailResetPassword($form->get('email')->getData());
            return FormResult::success();
        }

        return FormResult::failure($form);
    }

    /**
     * Handles the password reset form submission.
     * Validates the token and updates the user's password.
     */
    public function resetPassword(string $token, Request $request): FormResult
    {
        $user = $this->getUserByToken($token);

        if (!$user) {
          throw new InvalidTokenException('Invalid reset password token.');
        }

        $passwordResetSentTime = $user->getPasswordResetSentTime();
        if (!$passwordResetSentTime || $this->isTokenExpired($passwordResetSentTime, '1 hours')) {
            throw new TokenExpiredException('Token expired. Please request a new one.');
        }

        $form = $this->formFactory->create(PasswordResetType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('password')->getData();
            $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
            $user->setPasswordResetCode(null);
            $this->em->flush();

            return FormResult::success();
        }

        return FormResult::failure($form);

    }

    /**
     * Finds a user by their hashed password reset token.
     */
    private function getUserByToken(string $token)
    {
        $hashed = hash('sha256', $token);
        return $this->em->getRepository(User::class)->findOneBy([
            'passwordResetCode' => $hashed,
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
