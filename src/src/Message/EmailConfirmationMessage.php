<?php

namespace App\Message;

class EmailConfirmationMessage
{
    public function __construct(
        private int $userId,
        private string $token,
    ) {}

    /**
     * Returns the ID of the user who requested the password reset.
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Returns the secure reset token saved to the user record.
     */
    public function getToken(): string
    {
        return $this->token;
    }
}
