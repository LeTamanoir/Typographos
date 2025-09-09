<?php

declare(strict_types=1);

namespace Typographos\Types;

use Override;
use Typographos\Context\GenerationContext;
use Typographos\Context\RenderContext;
use Typographos\Exceptions\InvalidArgumentException;
use Typographos\Interfaces\Type;
use Typographos\TypeConverter;
use Typographos\Utils;

final class ArrayType implements Type
{
    private function __construct(
        private ArrayKind $kind,
        private Type $inner,
    ) {}

    #[Override]
    public function render(RenderContext $ctx): string
    {
        $innerRendered = $this->inner->render($ctx);

        if ($this->inner instanceof UnionType) {
            $innerRendered = "({$innerRendered})";
        }

        return $this->kind->render($innerRendered);
    }

    public static function from(GenerationContext $ctx, string $type): self
    {
        // handle bracket syntax like string[] or User[] or (string|int)[]
        $matches = null;
        if (preg_match('/^([a-zA-Z\\\\|\(\)]+)\[\]$/', $type, $matches)) {
            $innerType = $matches[1];

            if (preg_match('/^\((.+)\)$/', $innerType, $matches)) {
                $innerType = $matches[1];
            }

            $valueType = TypeConverter::convert($ctx, trim($innerType));
            return new self(ArrayKind::List, $valueType);
        }

        if (!preg_match('/^([a-z-]+)<(.+)>$/i', $type, $matches)) {
            throw InvalidArgumentException::fromCtx($ctx, 'Unsupported PHPDoc array type ' . trim($type));
        }

        [$_, $arrayTypeName, $typeArgs] = $matches;

        // parse generic array notation
        return match (strtolower($arrayTypeName)) {
            'list' => self::createList($ctx, $typeArgs, $type),
            'non-empty-list' => self::createNonEmptyList($ctx, $typeArgs, $type),
            'array' => self::createArray($ctx, $typeArgs, $type),
            default => throw InvalidArgumentException::fromCtx($ctx, 'Unsupported PHPDoc array type ' . trim($type)),
        };
    }

    /**
     * Create list<T> array type
     */
    private static function createList(GenerationContext $ctx, string $typeArgs, string $originalType): self
    {
        $types = Utils::splitTopLevel(trim($typeArgs), ',');
        if (count($types) !== 1) {
            throw InvalidArgumentException::fromCtx(
                $ctx,
                "Expected exactly one type argument when evaluating [{$originalType}]",
            );
        }

        $valueType = TypeConverter::convert($ctx, trim($types[0]));

        return new self(ArrayKind::List, $valueType);
    }

    /**
     * Create non-empty-list<T> array type
     */
    private static function createNonEmptyList(GenerationContext $ctx, string $typeArgs, string $originalType): self
    {
        $types = Utils::splitTopLevel(trim($typeArgs), ',');
        if (count($types) !== 1) {
            throw InvalidArgumentException::fromCtx(
                $ctx,
                "Expected exactly one type argument when evaluating [{$originalType}]",
            );
        }

        $valueType = TypeConverter::convert($ctx, trim($types[0]));

        return new self(ArrayKind::NonEmptyList, $valueType);
    }

    /**
     * Create array<K,V> type with key-value pairs
     */
    private static function createArray(GenerationContext $ctx, string $typeArgs, string $originalType): self
    {
        $types = Utils::splitTopLevel(trim($typeArgs), ',');
        if (count($types) !== 2) {
            throw InvalidArgumentException::fromCtx(
                $ctx,
                "Expected array<K,V> to have exactly two type args when evaluating [{$originalType}]",
            );
        }

        [$keyRaw, $valueRaw] = [trim($types[0]), trim($types[1])];

        $keyKind = ArrayKeyType::from($ctx, $keyRaw);
        $valueType = TypeConverter::convert($ctx, $valueRaw);

        return match ($keyKind) {
            ArrayKeyType::Int => new self(ArrayKind::List, $valueType),
            ArrayKeyType::String => new self(ArrayKind::IndexString, $valueType),
            ArrayKeyType::Both => new self(ArrayKind::IndexString, $valueType),
        };
    }
}
