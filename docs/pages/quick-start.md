# Quick Start

This guide will get you up and running with Typographos in minutes.

## Step 1: Annotate Your PHP Classes

Add the `#[TypeScript]` attribute to any PHP class you want to generate TypeScript types for:

```php
<?php

namespace App\DTO;

use Typographos\Attributes\TypeScript;

#[TypeScript]
class User
{
    public function __construct(
        public string $name,
        public int $age,
        public ?string $email = null,
        /** @var list<string> */
        public array $roles = [],
    ) {}
}
```

## Step 2: Create a Generator Script

Create a PHP script to generate your TypeScript types:

```php
<?php
// codegen.php

require 'vendor/autoload.php';

use Typographos\Generator;

$generator = new Generator();

// Option 1: Auto-discover classes from directory
$generator
    ->withDiscovery('src/DTO')  // Scan this directory
    ->withOutputPath('types.d.ts')
    ->generate();

// Option 2: Specify classes explicitly
$generator
    ->withOutputPath('types.d.ts')
    ->generate([
        App\DTO\User::class,
        App\DTO\Post::class,
    ]);
```

## Step 3: Run the Generator

Execute your generator script:

```bash
php codegen.php
```

## Step 4: View the Generated Types

Your `types.d.ts` file will contain:

```typescript
declare namespace App {
    export namespace DTO {
        export interface User {
            name: string
            age: number
            email: string | null
            roles: string[]
        }
    }
}
```

