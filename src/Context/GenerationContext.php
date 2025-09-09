<?php

declare(strict_types=1);

namespace Typographos\Context;

use ReflectionProperty;
use Typographos\Queue;

final class GenerationContext
{
    /**
     * @param  array<string, string>  $typeReplacements
     */
    public function __construct(
        public Queue $queue,

        public array $typeReplacements,

        public null|ReflectionProperty $parentProperty,
    ) {}
}
