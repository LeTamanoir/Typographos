<?php

declare(strict_types=1);

namespace Typographos\Types;

use Override;
use Typographos\Context\RenderContext;
use Typographos\Interfaces\Type;

final class RawType implements Type
{
    public function __construct(
        public string $rawExpr,
    ) {}

    #[Override]
    public function render(RenderContext $ctx): string
    {
        return $this->rawExpr;
    }
}
