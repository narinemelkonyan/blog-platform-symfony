<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
class NoForbiddenWords extends Constraint
{
    public string $message = 'Your comment contains forbidden words.';
}
