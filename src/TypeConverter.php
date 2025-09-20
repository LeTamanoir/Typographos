<?php

declare(strict_types=1);

namespace Typographos;

use ReflectionClass;
use Typographos\Attributes\InlineType;
use Typographos\Attributes\LiteralType;
use Typographos\Context\GenerationContext;
use Typographos\Interfaces\Type;
use Typographos\Types\ArrayType;
use Typographos\Types\InlineEnumType;
use Typographos\Types\InlineRecordType;
use Typographos\Types\RawType;
use Typographos\Types\ReferenceType;
use Typographos\Types\ScalarType;
use Typographos\Types\UnionType;

final class TypeConverter
{
    /**
     * Convert resolved PHP type to TypeScript type
     */
    public static function convert(GenerationContext $ctx, string $type): Type
    {
        $types = Utils::splitTopLevel($type, '|');
        $parts = [];

        if (count($types) === 0) {
            return ScalarType::unknown;
        }

        foreach ($types as $t) {
            $parts[] = self::convertType($ctx, $t);
        }

        if (count($parts) === 1) {
            /** @var Type */
            return $parts[0];
        }

        return new UnionType($parts);
    }

    /**
     * Convert a single PHP type to TypeScript
     */
    private static function convertType(GenerationContext $ctx, string $type): Type
    {
        if ($type === '') {
            return ScalarType::unknown;
        }

        $allowsNull = str_starts_with($type, '?');
        if ($allowsNull) {
            $type = substr($type, 1);
        }

        // check for type replacements first
        if (isset($ctx->typeReplacements[$type])) {
            $ts = new RawType($ctx->typeReplacements[$type]);

            if ($allowsNull) {
                return new UnionType([$ts, ScalarType::null]);
            }

            return $ts;
        }

        // handle #[LiteralType]
        if ($literal = self::hasLiteral($ctx)) {
            return $literal;
        }

        // handle built-in types
        if (Utils::isBuiltinType($type)) {
            $ts = Utils::isArrayType($type) ? ArrayType::from($ctx, $type) : ScalarType::from($ctx, $type);

            if ($allowsNull && !$ts->implicitlyNullable()) {
                return new UnionType([$ts, ScalarType::null]);
            }

            return $ts;
        }

        // handle user-defined classes
        $userDefined = class_exists($type) && new ReflectionClass($type)->isUserDefined();

        if (!$userDefined) {
            return ScalarType::unknown;
        }

        // check if the property has the InlineType attribute
        if (self::shouldInline($ctx)) {
            $ts = enum_exists($type) ? InlineEnumType::from($ctx, $type) : InlineRecordType::from($ctx, $type);
        } else {
            $ctx->queue->enqueue($type);
            $ts = new ReferenceType($type);
        }

        if ($allowsNull) {
            return new UnionType([$ts, ScalarType::null]);
        }

        return $ts;
    }

    private static function shouldInline(GenerationContext $ctx): bool
    {
        return $ctx->parentProperty !== null && count($ctx->parentProperty->getAttributes(InlineType::class)) > 0;
    }

    private static function hasLiteral(GenerationContext $ctx): null|RawType
    {
        if ($ctx->parentProperty !== null) {
            foreach ($ctx->parentProperty->getAttributes() as $attr) {
                $instance = $attr->newInstance();

                if ($instance instanceof LiteralType) {
                    return new RawType($instance->literal);
                }
            }
        }

        return null;
    }
}
