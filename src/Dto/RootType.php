<?php

declare(strict_types=1);

namespace Typographos\Dto;

use Override;
use Typographos\Interfaces\TypeScriptTypeInterface;
use Typographos\Utils;

final class RootType implements TypeScriptTypeInterface
{
    /**
     * @var array<string, NamespaceType>
     */
    public array $namespaces = [];

    /**
     * @var array<string, RecordType>
     */
    public array $records = [];

    public static function from(GenCtx $ctx): self
    {
        $root = new self;

        // process all classes in queue (queue may grow during processing)
        while ($className = $ctx->queue->shift()) {
            $record = RecordType::from($ctx, $className);

            $root->addRecord($className, $record);
        }

        return $root;
    }

    public function addRecord(string $fqcn, RecordType $record): void
    {
        // extract namespace: App\DTO\User → App\DTO
        $namespace = substr($fqcn, 0, strrpos($fqcn, '\\') ?: strlen($fqcn));

        $parts = Utils::fqcnParts($namespace);

        $node = &$this;
        foreach ($parts as $part) {
            if (! isset($node->namespaces[$part])) {
                $node->namespaces[$part] = new NamespaceType($part);
            }
            $node = &$node->namespaces[$part];
        }

        $node->records[$record->name] = $record;
    }

    #[Override]
    public function render(RenderCtx $ctx): string
    {
        $ts = '';

        foreach ($this->namespaces as $ns) {
            $ts .= $ns->render($ctx);
        }

        foreach ($this->records as $rec) {
            $ts .= $rec->render($ctx);
        }

        return $ts;
    }
}
