<?php

declare(strict_types=1);

namespace Typographos\Dto;

use Typographos\Enums\EnumStyle;

final class RenderCtx
{
    public function __construct(
        public string $indent = "\t",

        public int $depth = 0,

        public EnumStyle $enumStyle = EnumStyle::ENUMS,
    ) {}

    public function increaseDepth(): self
    {
        return new self(
            indent: $this->indent,
            depth: $this->depth + 1,
            enumStyle: $this->enumStyle,
        );
    }
}
