<?php


namespace App\Traits;

use Symfony\Component\HttpFoundation\Request;

/**
 * Trait for adding flash messages to request
 */
trait FlashMessageTrait
{
    /**
     * Add flash message
     */
    public function addFlashMessage(Request $request, string $context, string $message): void
    {
        if (null === $request->getSession()) {
            throw new \LogicException('Missing Session in request');
        }

        if (!in_array($context, ['notice', 'error'], true)) {
            throw new \LogicException('Invalid context - should be either notice, or error');
        }

        $request->getSession()->getFlashBag()->add($context, $message);
    }
}
