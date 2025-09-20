<?php

declare(strict_types=1);

namespace Typographos\Tests\Fixtures;

use Typographos\Attributes\Literal;
use Typographos\Attributes\Template;

class LiteralTypes
{
    public function __construct(
        #[Literal(42)]
        public int $literalNumber,
        #[Literal('hello')]
        public string $literalString,
        #[Literal(true)]
        public bool $literalBoolean,
        #[Template('template-{string}')]
        public string $templateLiteral,
        public string $regularProperty,
    ) {}
}