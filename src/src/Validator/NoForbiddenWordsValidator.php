<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NoForbiddenWordsValidator extends ConstraintValidator
{
    public function __construct(
        private readonly array $forbiddenWords,
    ) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof NoForbiddenWords) {
            throw new UnexpectedTypeException($constraint, NoForbiddenWords::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        $normalized = mb_strtolower((string) $value);

        foreach ($this->forbiddenWords as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/u', $normalized)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ word }}', $word)
                    ->addViolation();
                return;
            }
        }
    }
}
