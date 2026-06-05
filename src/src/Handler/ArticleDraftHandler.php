<?php

namespace App\Handler;

use App\Entity\Article;
use App\Entity\User;
use App\Enum\ArticleStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Handles autosave of article drafts via AJAX.
 */
class ArticleDraftHandler
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
    ) {}

    /**
     * Creates a new draft article from AJAX request data.
     */
    public function new(Request $request, User $user): array
    {
        $data = $this->getJsonData($request);
        if ($data === null) {
            return [
                'success' => false,
                'message' => 'Invalid request',
            ];
        }

        $article = new Article();
        $article->setTitle($data['title'] ?? 'Untitled');
        $article->setContent($data['content'] ?? '');
        $article->setStatus(ArticleStatus::DRAFT);
        $article->setAuthor($user);

        $errors = $this->validator->validate($article, null, ['Default']);

        if (count($errors) > 0) {
            return [
                'success' => false,
                'message' => $this->formatErrors($errors),
            ];
        }

        $this->em->persist($article);
        $this->em->flush();

        return [
            'success'    => true,
            'id'         => $article->getId(),
            'savedAt'    => (new \DateTimeImmutable())->format('H:i:s'),
        ];
    }

    /**
     * Updates an existing draft article from AJAX request data.
     */
    public function update(Request $request, Article $article, User $user): array
    {
        if (!$article->isAuthor($user)) {
            return [
                'success' => false,
                'message' => 'Access denied.',
            ];
        }

        if ($article->isPublished()) {
            return [
                'success' => false,
                'message' => 'Published articles cannot be autosaved as draft.',
            ];
        }

        $data = $this->getJsonData($request);

        if ($data === null) {
            return [
                'success' => false,
                'message' => 'Invalid request',
            ];

        }

        if (!empty($data['title']) && $data['title'] !== $article->getTitle()) {
            $article->setTitle($data['title']);
            $article->setSlug('');
        }

        if (isset($data['content'])) {
            $article->setContent($data['content']);
        }

        $this->em->flush();

        return [
            'success' => true,
            'savedAt' => (new \DateTimeImmutable())->format('H:i:s'),
        ];
    }

    private function getJsonData(Request $request): ?array
    {
        $data = json_decode($request->getContent(), true);
        return is_array($data) ? $data : null;
    }

    private function formatErrors(ConstraintViolationListInterface $errors): array
    {
        $result = [];
        foreach ($errors as $error) {
            $result[] = $error->getMessage();
        }
        return $result;
    }
}
