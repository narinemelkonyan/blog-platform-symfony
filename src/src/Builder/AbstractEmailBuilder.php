<?php

namespace App\Builder;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Base class for email builders.
 * Provides common dependencies for building templated emails.
 */
abstract class AbstractEmailBuilder implements EmailBuilderInterface
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected UrlGeneratorInterface $urlGenerator,
        protected string $mailerFrom,
    ) {}
}
