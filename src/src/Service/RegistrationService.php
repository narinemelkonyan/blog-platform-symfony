<?php

namespace App\Service;

use App\DTO\FormResult;
use App\Entity\User;
use App\Form\UserRegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/**
 * Handles user registration logic.
 */
class RegistrationService
{

    public function __construct(
        #[Autowire(service: 'limiter.register_limiter')]
        private readonly RateLimiterFactory $registerLimiter,
        private readonly EntityManagerInterface $em,
        private readonly FormFactoryInterface $formFactory,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EmailQueueService $emailQueueService,
    ) {}

    /**
     * Handles the registration form, hashes the password and persists the new user.
     * Sends a confirmation email after successful registration.
     */
    public function register(Request $request): FormResult
    {
        $limiter = $this->registerLimiter->create($request->getClientIp());
        if (!$limiter->consume(1)->isAccepted()) {
            return FormResult::rateLimitExceeded('Too many registration attempts. Please try again later.');
        }

        $user = new User();
        $form = $this->formFactory->create(UserRegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $user->getPlainPassword())
            );
            $user->eraseCredentials();
            $this->em->persist($user);
            $this->em->flush();
            $this->emailQueueService->sendEmailConfirmation($user);

            return FormResult::success();
        }

        return FormResult::failure($form);
    }
}
