<?php

declare(strict_types=1);

namespace Typographos\Traits;

use InvalidArgumentException;

/**
 * @template T
 */
trait HasChildrenTrait
{
    /**
     * @var array<string, T>
     */
    private array $children = [];

    /**
     * @return T|null
     */
    public function getChild(string $childKey)
    {
        return $this->children[$childKey] ?? null;
    }

    /**
     * @param  T  $type
     */
    public function addChild(string $childKey, mixed $type): self
    {
        $this->children[$childKey] = $type;

        return $this;
    }
}
