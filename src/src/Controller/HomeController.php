<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator
    ) {}
    #[Route('/', name: 'app_home')]

    public function index(): RedirectResponse
    {
        return new RedirectResponse(
            $this->urlGenerator->generate('app_article_index'),
            Response::HTTP_SEE_OTHER
        );
    }
}
