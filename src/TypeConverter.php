<?php

declare(strict_types=1);

namespace Typographos;

use ReflectionClass;
use Typographos\Attributes\Inline;
use Typographos\Attributes\Literal;
use Typographos\Attributes\Template;
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

        // check for #[Literal] and #[Template] attributes first
        if ($attrType = self::checkAttributes($ctx, $allowsNull)) {
            return $attrType;
        }

        // check for type replacements
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
        return $ctx->parentProperty !== null && count($ctx->parentProperty->getAttributes(Inline::class)) > 0;
    }

    private static function checkAttributes(GenerationContext $ctx, bool $allowsNull): null|Type
    {
        if ($ctx->parentProperty === null) {
            return null;
        }

        // Check for #[Literal] attribute
        $literalAttrs = $ctx->parentProperty->getAttributes(Literal::class);
        if (count($literalAttrs) === 1) {
            $literal = $literalAttrs[0]->newInstance();
            $value = match (gettype($literal->value)) {
                'string' => self::shouldQuoteString($literal->value) ? '"' . $literal->value . '"' : $literal->value,
                'boolean' => $literal->value ? 'true' : 'false',
                'NULL' => 'null',
                default => (string) $literal->value,
            };
            $ts = new RawType($value);

            return $allowsNull ? new UnionType([$ts, ScalarType::null]) : $ts;
        }

        // Check for #[Template] attribute
        $templateAttrs = $ctx->parentProperty->getAttributes(Template::class);
        if (count($templateAttrs) === 1) {
            $template = $templateAttrs[0]->newInstance();
            $pattern = str_replace('{string}', '${string}', $template->pattern);
            $pattern = str_replace('{number}', '${number}', $pattern);
            $ts = new RawType('`' . $pattern . '`');

            return $allowsNull ? new UnionType([$ts, ScalarType::null]) : $ts;
        }

        return null;
    }

    private static function shouldQuoteString(string $value): bool
    {
        // Don't quote strings that look like TypeScript identifiers/references (e.g., "MyEnum.VALUE")
        return !preg_match('/^[A-Z][a-zA-Z0-9]*\.[A-Z_][A-Z0-9_]*$/', $value);
    }
}
