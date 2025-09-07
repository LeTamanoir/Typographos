<?php

declare(strict_types=1);

namespace Typographos\Attributes;

use Attribute;

/**
 * LiteralType attribute to mark a property that is a literal type.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class LiteralType
{
    public function __construct(
        public string $literal,
    ) {}
}
