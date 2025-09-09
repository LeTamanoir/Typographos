<?php

declare(strict_types=1);

namespace Typographos\Context;

use Typographos\Enums\EnumStyle;
use Typographos\Enums\RecordStyle;

final readonly class RenderContext
{
    public function __construct(
        public string $indent,

        public int $depth,

        public EnumStyle $enumStyle,

        public RecordStyle $recordStyle,
    ) {}

    public function increaseDepth(): self
    {
        return new self(
            indent: $this->indent,
            depth: $this->depth + 1,
            enumStyle: $this->enumStyle,
            recordStyle: $this->recordStyle,
        );
    }
}
