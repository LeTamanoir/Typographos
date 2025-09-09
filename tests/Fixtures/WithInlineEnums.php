<?php

declare(strict_types=1);

namespace Typographos\Tests\Fixtures;

use Typographos\Attributes\InlineType;

class WithInlineEnums
{
    public function __construct(
        #[InlineType]
        public StringEnum $inlineStringEnum,
        #[InlineType]
        public IntEnum $inlineIntEnum,
        public StringEnum $regularStringEnum,
        public IntEnum $regularIntEnum,
    ) {}
}