<?php

namespace App\DTO;

use Symfony\Component\Form\FormInterface;

/**
 * Represents the result of a form handling operation.
 */
class FormResult
{
    public function __construct(
        private readonly bool $success,
        private readonly ?FormInterface $form = null,
        private readonly ?string $redirectRoute = null,
        private readonly ?string $rateLimitMessage = null,
        private readonly mixed $data = null,
        private readonly bool $emailChanged = false,
    )
    {}

    /**
     * Creates a successful result.
     */
    public static function success(mixed $data = null): self
    {
        return new self(true, null, null, null, $data);
    }

    /**
     * Creates a failed result with the form containing validation errors.
     */
    public static function failure(FormInterface $form): self
    {
       return new self(false, $form);
    }

    /**
     * Creates a rate limit exceeded result with an error message.
     */
    public static function rateLimitExceeded(string $message): self
    {
        return new self(false, null, null, $message);
    }

    /**
     * Returns true if the form was handled successfully.
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Returns the form with validation errors, or null on success.
     */
    public function getForm(): ?FormInterface
    {
        return $this->form;
    }

    /**
     * Returns the redirect route name, or null if not set.
     */
    public function getRedirectRoute(): ?string
    {
        return $this->redirectRoute;
    }

    /**
     * Returns true if the rate limit has been exceeded.
     */
    public function isRateLimitExceeded(): bool
    {
        return $this->rateLimitMessage !== null;
    }

    /**
     * Returns the rate limit error message, or null if not exceeded.
     */
    public function getRateLimitMessage(): ?string
    {
        return $this->rateLimitMessage;
    }

    /**
     * Returns the result data payload, or null if not set.
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * Creates a successful result indicating the email address was changed and requires confirmation.
     */
    public static function successWithEmailChange(): self
    {
        return new self(true, null, null, null, null, true);
    }

    /**
     * Returns true if the email address was changed and requires confirmation.
     */
    public function isEmailChanged(): bool
    {
        return $this->emailChanged;
    }

}
