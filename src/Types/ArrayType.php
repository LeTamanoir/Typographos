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
        return $this->kind->render($this->inner->render($ctx));
    }

    public static function from(GenerationContext $ctx, string $type): self
    {
        // Parse generic array notation
        $matches = null;
        if (!preg_match('/^([a-z-]+)<(.+)>$/i', $type, $matches)) {
            throw InvalidArgumentException::fromCtx($ctx, 'Unsupported PHPDoc array type ' . trim($type));
        }

        [$_, $arrayTypeName, $typeArgs] = $matches;

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
