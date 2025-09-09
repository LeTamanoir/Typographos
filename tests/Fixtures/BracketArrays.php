<?php

declare(strict_types=1);

namespace Typographos\Tests\Fixtures;

class BracketArrays
{
    public function __construct(
        /** @var (string|int)[] */
        public array $stringArray,
        /** @var int[] */
        public array $intArray,
        /** @var \Typographos\Tests\Fixtures\Scalars[] */
        public array $objectArray,
    ) {}
}
