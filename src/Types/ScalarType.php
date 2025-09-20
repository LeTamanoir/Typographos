<?php

declare(strict_types=1);

namespace Typographos\Types;

use Override;
use Typographos\Context\GenerationContext;
use Typographos\Context\RenderContext;
use Typographos\Exceptions\InvalidArgumentException;
use Typographos\Interfaces\Type;

enum ScalarType implements Type
{
    case boolean;
    case number;
    case unknown;
    case object;
    case null;
    case string;
    case true;
    case false;
    case any;

    public static function from(GenerationContext $ctx, string $phpScalar): self
    {
        return match ($phpScalar) {
            'int', 'float' => self::number,
            'string' => self::string,
            'bool' => self::boolean,
            'object' => self::object,
            'mixed' => self::any,
            'null' => self::null,
            'true' => self::true,
            'false' => self::false,
            default => throw InvalidArgumentException::fromCtx($ctx, 'Unsupported scalar type ' . $phpScalar),
        };
    }

    public function implicitlyNullable(): bool
    {
        return match ($this) {
            self::null, self::any, self::unknown => true,
            default => false,
        };
    }

    #[Override]
    public function render(RenderContext $ctx): string
    {
        return $this->name;
    }
}
