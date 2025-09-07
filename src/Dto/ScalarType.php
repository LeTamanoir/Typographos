<?php

declare(strict_types=1);

namespace Typographos\Dto;

use Override;
use ReflectionClass;
use Typographos\Attributes\LiteralType;
use Typographos\Exceptions\InvalidArgumentException;
use Typographos\Interfaces\TypeScriptTypeInterface;

enum ScalarType implements TypeScriptTypeInterface
{
    case boolean;
    case number;
    case unknown;
    case object;
    case null;
    case undefined;
    case string;
    case true;
    case false;
    case any;
    case never;

    public static function from(GenCtx $ctx, string $phpScalar): self|RawType
    {
        if ($ctx->parentProperty !== null) {
            $attrs = $ctx->parentProperty->getAttributes();
            foreach ($attrs as $attr) {
                if ($attr->getName() === LiteralType::class || is_subclass_of($attr->getName(), LiteralType::class)) {
                    return new RawType($attr->newInstance()->literal);
                }
            }
        }

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

    #[Override]
    public function render(RenderCtx $ctx): string
    {
        return $this->name;
    }
}
