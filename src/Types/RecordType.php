<?php

declare(strict_types=1);

namespace Typographos\Types;

use Override;
use ReflectionClass;
use ReflectionProperty;
use Typographos\Context\GenerationContext;
use Typographos\Context\RenderContext;
use Typographos\Enums\RecordStyle;
use Typographos\Interfaces\Type;
use Typographos\TypeConverter;
use Typographos\TypeResolver;
use Typographos\Utils;

final class RecordType implements Type
{
    /**
     * @var array<string, Type>
     */
    private array $properties = [];

    public function __construct(
        public string $name,
    ) {}

    public function addProperty(string $propertyKey, Type $property): self
    {
        $this->properties[$propertyKey] = $property;

        return $this;
    }

    public static function from(GenerationContext $ctx, string $className): self
    {
        $ref = new ReflectionClass($className);
        $record = new RecordType($ref->getShortName());

        foreach ($ref->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $propName = $prop->getName();

            $type = TypeResolver::resolve($prop);

            $ctx->parentProperty = $prop;
            $ts = TypeConverter::convert($ctx, $type);
            $ctx->parentProperty = null;

            $record->addProperty($propName, $ts);
        }

        return $record;
    }

    #[Override]
    public function render(RenderContext $ctx): string
    {
        $indent = str_repeat($ctx->indent, $ctx->depth);
        $propIndent = $indent . $ctx->indent;

        if ($ctx->recordStyle === RecordStyle::INTERFACES) {
            $ts = $indent . 'export interface ' . $this->name . " {\n";
        } else {
            $ts = $indent . 'export type ' . $this->name . " = {\n";
        }

        foreach ($this->properties as $name => $type) {
            $ts .= $propIndent . Utils::tsProp($name) . ': ' . $type->render($ctx) . "\n";
        }

        $ts .= $indent . "}\n";

        return $ts;
    }
}
