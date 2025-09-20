<?php

declare(strict_types=1);

namespace Typographos\Tests\Fixtures;

use Typographos\Attributes\Inline;

final class InlineRecords
{
    public function __construct(
        #[Inline]
        public Scalars $inlineScalars,
        public Scalars $scalars,
    ) {}
}
