<?php

declare(strict_types=1);

namespace Typographos\Interfaces;

use Typographos\Context\RenderContext;

interface Type
{
    public function render(RenderContext $ctx): string;
}
