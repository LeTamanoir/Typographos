<?php

declare(strict_types=1);

namespace Typographos\Dto;

use Override;
use Typographos\Interfaces\TypeScriptTypeInterface;

final class NamespaceType implements TypeScriptTypeInterface
{
    /**
     * @var array<string, NamespaceType>
     */
    public array $namespaces = [];

    /**
     * @var array<string, RecordType>
     */
    public array $records = [];

    public function __construct(
        public string $name,
    ) {}

    #[Override]
    public function render(RenderCtx $ctx): string
    {
        $indent = str_repeat($ctx->indent, $ctx->depth);

        $declaration = $ctx->depth === 0 ? 'declare namespace' : 'export namespace';

        $ts = $indent . $declaration . ' ' . $this->name . " {\n";

        foreach ($this->namespaces as $ns) {
            $ts .= $ns->render($ctx->increaseDepth());
        }

        foreach ($this->records as $rec) {
            $ts .= $rec->render($ctx->increaseDepth());
        }

        $ts .= $indent . "}\n";

        return $ts;
    }
}
