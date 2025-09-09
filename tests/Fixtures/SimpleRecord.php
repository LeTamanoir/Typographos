<?php

declare(strict_types=1);

namespace Typographos\Tests\Fixtures;

class SimpleRecord
{
    public function __construct(
        public string $name,
        public int $age,
    ) {}
}