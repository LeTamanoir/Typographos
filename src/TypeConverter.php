<?php

declare(strict_types=1);

namespace Typographos;

use ReflectionClass;
use Typographos\Attributes\InlineType;
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

        // handle built-in types
        if (Utils::isBuiltinType($type)) {
            $ts = Utils::isArrayType($type) ? ArrayType::from($ctx, $type) : ScalarType::from($ctx, $type);

            if ($allowsNull && $type !== 'null' && $type !== 'mixed') {
                return new UnionType([$ts, ScalarType::null]);
            }

            return $ts;
        }

        // handle user-defined classes
        $userDefined = class_exists($type) && new ReflectionClass($type)->isUserDefined();

        if ($userDefined) {
            // check if the property has the InlineType attribute
            $shouldInline =
                $ctx->parentProperty !== null && count($ctx->parentProperty->getAttributes(InlineType::class)) > 0;

            if ($shouldInline) {
                $ts = new ReflectionClass($type)->isEnum()
                    ? InlineEnumType::from($ctx, $type)
                    : InlineRecordType::from($ctx, $type);
            } else {
                $ctx->queue->enqueue($type);
                $ts = new ReferenceType($type);
            }
        } else {
            $ts = ScalarType::unknown;
        }

        if ($allowsNull) {
            return new UnionType([$ts, ScalarType::null]);
        }

        return $ts;
    }
}
