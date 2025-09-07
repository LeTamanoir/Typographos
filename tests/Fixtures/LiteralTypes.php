<?php

declare(strict_types=1);

namespace Typographos\Tests\Fixtures;

use Typographos\Attributes\LiteralType;

class LiteralTypes
{
    public function __construct(
        #[LiteralType('42')]
        public int $literalNumber,
        #[LiteralType('"hello"')]
        public string $literalString,
        #[LiteralType('true')]
        public bool $literalBoolean,
        #[LiteralType('MyEnum.VALUE')]
        public mixed $enumReference,
        #[LiteralType('`template-${string}`')]
        public string $templateLiteral,
        #[LiteralType('null')]
        public mixed $literalNull,
        public string $regularProperty,
    ) {}
}