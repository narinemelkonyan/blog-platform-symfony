<?php

namespace App\Controller;

use App\Exception\InvalidTokenException;
use App\Exception\TokenExpiredException;
use App\Form\UserLoginType;
use App\Service\EmailConfirmationService;
use App\Service\PasswordResetService;
use App\Service\RegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly RegistrationService $registrationService,
        private readonly EmailConfirmationService $emailConfirmationService,
        private readonly PasswordResetService $passwordResetService,
    ) {}

    /**
     * User logging page.
     */
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_article_index');
        }

        $form = $this->createForm(UserLoginType::class, null, [
            'data' => ['email' => $authenticationUtils->getLastUsername()],
        ]);

        return $this->render('security/login.html.twig', [
            'form'  => $form,
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * User register page.
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        $result = $this->registrationService->register($request);

        if ($result->isRateLimitExceeded()) {
            $this->addFlash('error', $result->getRateLimitMessage());
            return $this->redirectToRoute('app_login');
        }

        if ($result->isSuccess()) {
            $this->addFlash('notice', 'Registration successful! Please check your email to confirm your account.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', ['form' => $result->getForm()]);
    }

    /**
     * Confirm user action.
     */
    #[Route('/confirm/{token}', name: 'app_email_confirm')]
    public function confirm(string $token): Response
    {
        try {
            $this->emailConfirmationService->confirm($token);
            $this->addFlash('notice', 'Email confirmed! You can now log in.');
            return $this->redirectToRoute('app_login');
        }
        catch (InvalidTokenException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_login');
        }
        catch (TokenExpiredException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_register');
        }
    }

    /**
     * User password reset request page.
     */
    #[Route('/password-reset-request', name: 'app_password_reset_request')]
    public function resetPasswordRequest(Request $request): Response
    {
     $result = $this->passwordResetService->reset($request);

     if ($result->isRateLimitExceeded()) {
         $this->addFlash('error', $result->getRateLimitMessage());
         return $this->redirectToRoute('app_login');
     }

     if ($result->isSuccess()) {
         $this->addFlash('notice', 'Your password reset request has been sent. Please check your email.');
         return $this->redirectToRoute('app_login');
     }


     return $this->render('security/password-reset-request.html.twig', ['form' => $result->getForm()]);
    }

    /**
     * User password reset page.
     */
    #[Route('/password-reset/{token}', name: 'app_reset_password')]
    public function resetPassword(string $token, Request $request): Response
    {
        try {
            $result = $this->passwordResetService->resetPassword($token, $request);
        } catch (InvalidTokenException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_login');
        } catch (TokenExpiredException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_password_reset_request');
        }

        if ($result->isSuccess()) {
            $this->addFlash('notice', 'You can now log in.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/password-reset.html.twig', [
            'form' => $result->getForm(),
        ]);
    }

    /**
     * User logout action.
     */
    #[Route('/logout', name: 'app_logout')]
    public function logout(): never
    {
        throw new \LogicException('This method should not be reached.');
    }
}
