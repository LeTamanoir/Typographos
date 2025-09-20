<?php

declare(strict_types=1);

namespace Typographos\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class Template
{
    public function __construct(
        public string $pattern,
    ) {}
}