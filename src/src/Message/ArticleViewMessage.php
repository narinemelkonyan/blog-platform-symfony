<?php

namespace App\Message;

/**
 * Message for incrementing article view counter asynchronously.
 */
final readonly class ArticleViewMessage
{
    public function __construct(
        public int $articleId,
    ) {}
}
