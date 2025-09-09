<?php

declare(strict_types=1);

namespace Typographos\Types;

use Override;
use Typographos\Context\RenderContext;
use Typographos\Interfaces\Type;
use Typographos\Utils;

/**
 * ReferenceType represents a reference to another TypeScript type.
 */
final class ReferenceType implements Type
{
    public function __construct(
        public string $fqcn,
    ) {}

    #[Override]
    public function render(RenderContext $ctx): string
    {
        return Utils::tsFqcn($this->fqcn);
    }
}
