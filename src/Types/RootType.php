<?php

declare(strict_types=1);

namespace Typographos\Types;

use Override;
use ReflectionClass;
use Typographos\Context\GenerationContext;
use Typographos\Context\RenderContext;
use Typographos\Interfaces\Type;
use Typographos\TypeConverter;
use Typographos\Utils;

final class RootType implements Type
{
    /**
     * @var array<string, NamespaceType>
     */
    public array $namespaces = [];

    /**
     * @var array<string, Type>
     */
    public array $types = [];

    public static function from(GenerationContext $ctx): self
    {
        $root = new self();

        // process all classes in queue (queue may grow during processing)
        while ($className = $ctx->queue->shift()) {
            $type = new ReflectionClass($className)->isEnum()
                ? EnumType::from($ctx, $className)
                : RecordType::from($ctx, $className);

            $root->addType($className, $type);
        }

        return $root;
    }

    public function addType(string $fqcn, RecordType|EnumType $type): void
    {
        // extract namespace: App\DTO\User → App\DTO
        $namespace = substr($fqcn, 0, strrpos($fqcn, '\\') ?: strlen($fqcn));

        $parts = Utils::fqcnParts($namespace);

        $node = &$this;
        foreach ($parts as $part) {
            if (!isset($node->namespaces[$part])) {
                $node->namespaces[$part] = new NamespaceType($part);
            }
            $node = &$node->namespaces[$part];
        }

        $node->types[$type->name] = $type;
    }

    #[Override]
    public function render(RenderContext $ctx): string
    {
        $ts = '';

        foreach ($this->namespaces as $ns) {
            $ts .= $ns->render($ctx);
        }

        foreach ($this->types as $type) {
            $ts .= $type->render($ctx);
        }

        return $ts;
    }
}
