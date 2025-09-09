<?php

declare(strict_types=1);

namespace Typographos\Types;

use Override;
use Typographos\Context\GenerationContext;
use Typographos\Context\RenderContext;
use Typographos\Interfaces\Type;

final class UnionType implements Type
{
    /**
     * @param  Type[]  $types
     */
    public function __construct(
        private array $types,
    ) {}

    #[Override]
    public function render(RenderContext $ctx): string
    {
        $seen = [];

        foreach ($this->types as $type) {
            $render = $type->render($ctx);
            $seen[$render] = true;
        }

        return implode(' | ', array_keys($seen));
    }
}
