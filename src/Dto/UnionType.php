<?php

declare(strict_types=1);

namespace Typographos\Dto;

use Override;
use Typographos\Interfaces\TypeScriptTypeInterface;

final class UnionType implements TypeScriptTypeInterface
{
    /**
     * @param  TypeScriptTypeInterface[]  $types
     */
    public function __construct(
        private array $types,
    ) {}

    #[Override]
    public function render(RenderCtx $ctx): string
    {
        $seen = [];

        foreach ($this->types as $type) {
            $render = $type->render($ctx);
            $seen[$render] = true;
        }

        return implode(' | ', array_keys($seen));
    }
}
