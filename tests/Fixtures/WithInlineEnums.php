<?php

declare(strict_types=1);

namespace Typographos\Tests\Fixtures;

use Typographos\Attributes\Inline;

class WithInlineEnums
{
    public function __construct(
        #[Inline]
        public StringEnum $inlineStringEnum,
        #[Inline]
        public IntEnum $inlineIntEnum,
        public StringEnum $regularStringEnum,
        public IntEnum $regularIntEnum,
    ) {}
}