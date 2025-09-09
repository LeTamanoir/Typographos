<?php

declare(strict_types=1);

namespace Typographos\Types;

use Override;
use Typographos\Context\RenderContext;
use Typographos\Interfaces\Type;

final class NamespaceType implements Type
{
    /**
     * @var array<string, NamespaceType>
     */
    public array $namespaces = [];

    /**
     * @var array<string, Type>
     */
    public array $types = [];

    public function __construct(
        public string $name,
    ) {}

    #[Override]
    public function render(RenderContext $ctx): string
    {
        $indent = str_repeat($ctx->indent, $ctx->depth);

        $declaration = $ctx->depth === 0 ? 'declare namespace' : 'export namespace';

        $ts = $indent . $declaration . ' ' . $this->name . " {\n";

        foreach ($this->namespaces as $ns) {
            $ts .= $ns->render($ctx->increaseDepth());
        }

        foreach ($this->types as $t) {
            $ts .= $t->render($ctx->increaseDepth());
        }

        $ts .= $indent . "}\n";

        return $ts;
    }
}
