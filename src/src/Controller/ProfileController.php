<?php

namespace App\Controller;

use App\Handler\ProfileHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profile')]
class ProfileController extends AbstractController
{
    public function __construct(
        private readonly ProfileHandler $handler
    ) {}

    /**
     * Handles user profile viewing and editing.
     */
    #[Route('/', name: 'app_profile', methods: ['GET', 'POST'])]
    public function profile(Request $request): Response
    {
        $result = $this->handler->profile($request, $this->getUser());

        if($result->isEmailChanged()){
            $this->addFlash('notice', 'Please confirm your new email.');
            return $this->redirectToRoute('app_logout');

        }

        if ($result->isSuccess()) {
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/profile.html.twig', ['form' => $result->getForm(), 'user' => $this->getUser()]);
    }
}
