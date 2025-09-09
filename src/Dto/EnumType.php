<?php

declare(strict_types=1);

namespace Typographos\Dto;

use Override;
use ReflectionEnum;
use ReflectionEnumUnitCase;
use Typographos\Enums\EnumStyle;
use Typographos\Interfaces\TypeScriptTypeInterface;
use Typographos\Utils;

final class EnumType implements TypeScriptTypeInterface
{
    /**
     * @var array<string, string|int>
     */
    public array $cases = [];

    public function __construct(
        public string $name,
    ) {}

    public function addCase(string $name, string|int $value): self
    {
        $this->cases[$name] = $value;
        return $this;
    }

    public static function from(GenCtx $ctx, string $className): self
    {
        $ref = new ReflectionEnum($className);
        $enum = new self($ref->getShortName());

        foreach ($ref->getCases() as $case) {
            $enum->addCase($case->getName(), $case->getValue()->value);
        }

        return $enum;
    }

    #[Override]
    public function render(RenderCtx $ctx): string
    {
        $indent = str_repeat($ctx->indent, $ctx->depth);
        $propIndent = $indent . $ctx->indent;

        if ($ctx->enumStyle === EnumStyle::TYPES) {
            $ts = $indent . 'export type ' . $this->name . ' = ';
        } else {
            $ts = $indent . 'export enum ' . $this->name . " {\n";
        }

        foreach ($this->cases as $name => $value) {
            if (is_string($value)) {
                $value = "\"{$value}\"";
            }
            if ($ctx->enumStyle === EnumStyle::TYPES) {
                $ts .= $value . ' | ';
            } else {
                $ts .= $propIndent . Utils::tsProp($name) . ' = ' . $value . ",\n";
            }
        }

        if ($ctx->enumStyle === EnumStyle::TYPES) {
            $ts = rtrim($ts, ' | ') . ";\n";
        } else {
            $ts .= $indent . "}\n";
        }

        return $ts;
    }
}
