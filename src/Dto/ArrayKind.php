<?php

declare(strict_types=1);

namespace Typographos\Dto;

enum ArrayKind
{
    case List;
    case NonEmptyList;
    case IndexString;

    public function render(string $inner): string
    {
        return match ($this) {
            self::List => $inner.'[]',
            self::NonEmptyList => '['.$inner.', ...'.$inner.'[]]',
            self::IndexString => '{ [key: string]: '.$inner.' }',
        };
    }
}
