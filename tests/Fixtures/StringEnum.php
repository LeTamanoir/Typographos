<?php

declare(strict_types=1);

namespace Typographos\Tests\Fixtures;

enum StringEnum: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';  
    case INACTIVE = 'inactive';
}