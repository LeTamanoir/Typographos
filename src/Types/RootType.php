<?php

declare(strict_types=1);

namespace Typographos\Types;

use Override;
use Typographos\Context\RenderContext;
use Typographos\Interfaces\Type;
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

    /**
     * @param array<string, RecordType|EnumType> $types
     */
    public static function fromTypes(array $types): self
    {
        $root = new self();

        foreach ($types as $fqcn => $type) {
            $root->addType($fqcn, $type);
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
