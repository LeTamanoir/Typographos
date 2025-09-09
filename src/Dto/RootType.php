<?php

declare(strict_types=1);

namespace Typographos\Dto;

use Override;
use ReflectionClass;
use Typographos\Interfaces\TypeScriptTypeInterface;
use Typographos\Utils;

final class RootType implements TypeScriptTypeInterface
{
    /**
     * @var array<string, NamespaceType>
     */
    public array $namespaces = [];

    /**
     * @var array<string, TypeScriptTypeInterface>
     */
    public array $types = [];

    public static function from(GenCtx $ctx): self
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

    public function addType(string $fqcn, TypeScriptTypeInterface $type): void
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
    public function render(RenderCtx $ctx): string
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
