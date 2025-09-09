<?php

declare(strict_types=1);

namespace Typographos\Exceptions;

use InvalidArgumentException as BaseInvalidArgumentException;
use ReflectionProperty;
use Typographos\Context\GenerationContext;

class InvalidArgumentException
{
    public static function fromCtx(GenerationContext $ctx, string $message): BaseInvalidArgumentException
    {
        $location = '';

        if ($ctx->parentProperty) {
            $location .= self::getLocation($ctx->parentProperty);
        }

        return new BaseInvalidArgumentException($message . ' ' . $location);
    }

    public static function fromProp(ReflectionProperty $prop, string $message): BaseInvalidArgumentException
    {
        return new BaseInvalidArgumentException($message . ' ' . self::getLocation($prop));
    }

    private static function getLocation(ReflectionProperty $prop): string
    {
        $declClass = $prop->getDeclaringClass();

        return (
            'for property $'
            . $prop->getName()
            . ' in '
            . $declClass->getName()
            . ' ('
            . $declClass->getFileName()
            . ':'
            . $declClass->getStartLine()
            . ')'
        );
    }
}
