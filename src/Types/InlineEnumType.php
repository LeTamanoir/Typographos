<?php

declare(strict_types=1);

namespace Typographos\Types;

use BackedEnum;
use InvalidArgumentException;
use Override;
use ReflectionEnum;
use Typographos\Context\GenerationContext;
use Typographos\Context\RenderContext;
use Typographos\Interfaces\Type;

final class InlineEnumType implements Type
{
    /**
     * @var array<string, string|int>
     */
    public array $cases = [];

    public function addCase(string $name, string|int $value): self
    {
        $this->cases[$name] = $value;

        return $this;
    }

    public static function from(GenerationContext $_ctx, string $className): self
    {
        $ref = new ReflectionEnum($className);
        $enum = new self();

        foreach ($ref->getCases() as $case) {
            $value = $case->getValue();
            if ($value instanceof BackedEnum) {
                $enum->addCase($case->getName(), $value->value);
            }
        }

        return $enum;
    }

    #[Override]
    public function render(RenderContext $ctx): string
    {
        $ts = '';

        foreach ($this->cases as $value) {
            if (is_string($value)) {
                $value = "\"{$value}\"";
            }
            $ts .= $value . ' | ';
        }

        $ts = rtrim($ts, ' | ') . ';';

        return $ts;
    }
}
