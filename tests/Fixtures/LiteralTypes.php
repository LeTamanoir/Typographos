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
        #[Literal('MyEnum.VALUE')]
        public mixed $enumReference,
        #[Template('template-{string}')]
        public string $templateLiteral,
        #[Literal(null)]
        public mixed $literalNull,
        public string $regularProperty,
    ) {}
}