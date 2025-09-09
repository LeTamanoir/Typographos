<?php

declare(strict_types=1);

namespace Typographos\Tests\Fixtures;

class WithEnums
{
    public function __construct(
        public StringEnum $regularStatus,
        public IntEnum $regularPriority,
    ) {}
}