<?php

declare(strict_types=1);

namespace Typographos\Dto;

use Override;
use ReflectionClass;
use ReflectionProperty;
use Typographos\Interfaces\TypeScriptTypeInterface;
use Typographos\TypeConverter;
use Typographos\TypeResolver;
use Typographos\Utils;

final class InlineRecordType implements TypeScriptTypeInterface
{
    /**
     * @var array<string, TypeScriptTypeInterface>
     */
    private array $properties = [];

    public function addProperty(string $propertyKey, TypeScriptTypeInterface $property): self
    {
        $this->properties[$propertyKey] = $property;

        return $this;
    }

    public static function from(GenCtx $ctx, string $className): self
    {
        $ref = new ReflectionClass($className);
        $record = new InlineRecordType();

        foreach ($ref->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $propName = $prop->getName();

            $type = TypeResolver::resolve($prop);

            $ts = TypeConverter::convert($ctx, $type);

            $record->addProperty($propName, $ts);
        }

        return $record;
    }

    #[Override]
    public function render(RenderCtx $ctx): string
    {
        $indent = str_repeat($ctx->indent, $ctx->depth + 1);
        $propIndent = $indent . $ctx->indent;

        $ts = "{\n";

        foreach ($this->properties as $name => $type) {
            $ts .= $propIndent . Utils::tsProp($name) . ': ' . $type->render($ctx) . "\n";
        }

        $ts .= $indent . '}';

        return $ts;
    }
}
