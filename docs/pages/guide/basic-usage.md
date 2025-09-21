# Basic Usage

Learn the fundamentals of using Typographos to generate TypeScript types from your PHP classes.

## The TypeScript Attribute

The `#[TypeScript]` attribute is the cornerstone of Typographos. Add it to any PHP class you want to generate TypeScript types for:

```php
<?php

use Typographos\Attributes\TypeScript;

#[TypeScript]
class User
{
    public function __construct(
        public string $name,
        public int $age,
    ) {}
}
```

## Generator Methods

The `Generator` class provides a fluent interface for configuring and running type generation:

### Auto-Discovery

Scan directories for classes with the `#[TypeScript]` attribute:

```php
use Typographos\Generator;

$generator = new Generator();
$generator
    ->withDiscovery('src/DTO')        // Single directory
    ->withDiscovery('app/Models')     // Multiple calls are additive
    ->withOutputPath('types.d.ts')
    ->generate();
```

### Explicit Class Lists

Specify exactly which classes to process:

```php
$generator = new Generator();
$generator
    ->withOutputPath('types.d.ts')
    ->generate([
        App\DTO\User::class,
        App\DTO\Post::class,
        App\DTO\Comment::class,
    ]);
```

### Output Options

Control where and how the TypeScript is generated:

```php
$generator = new Generator();

// Write to file
$generator
    ->withOutputPath('resources/js/types.d.ts')
    ->generate([User::class]);

// Get as string (don't write to file)
$typeScript = $generator->generate([User::class]);
echo $typeScript;
```

## Supported PHP Types

Typographos automatically converts PHP types to their TypeScript equivalents:

| PHP Type | TypeScript Type |
|----------|----------------|
| `string` | `string` |
| `int` | `number` |
| `float` | `number` |
| `bool` | `boolean` |
| `array` | `unknown[]` (requires PHPDoc) |
| `?string` | `string \| null` |
| `string\|int` | `string \| number` |
| `self` | Current class name |
| `parent` | Parent class name |

## Property Visibility

Only **public properties** are included in the generated TypeScript. Private and protected properties are ignored:

```php
#[TypeScript]
class User
{
    public function __construct(
        public string $name,        // ✅ Included
        protected int $age,         // ❌ Ignored
        private string $password,   // ❌ Ignored
    ) {}
}
```

Generated TypeScript:
```typescript
export interface User {
    name: string
    // age and password are not included
}
```

## Untyped Properties

Properties without type hints are emitted as `unknown`:

```php
#[TypeScript]
class Data
{
    public function __construct(
        public string $name,     // string
        public $value,           // unknown
    ) {}
}
```

Generated TypeScript:
```typescript
export interface Data {
    name: string
    value: unknown
}
```

## Namespace Handling

PHP namespaces are preserved as nested TypeScript namespaces:

```php
namespace App\DTO\User;

#[TypeScript]
class Profile
{
    public function __construct(
        public string $bio,
    ) {}
}
```

Generated TypeScript:
```typescript
declare namespace App {
    export namespace DTO {
        export namespace User {
            export interface Profile {
                bio: string
            }
        }
    }
}
```

## Class Dependencies

When a class references another class, Typographos automatically includes both in the output:

```php
#[TypeScript]
class Address
{
    public function __construct(
        public string $street,
        public string $city,
    ) {}
}

#[TypeScript]
class User
{
    public function __construct(
        public string $name,
        public Address $address,  // References Address class
    ) {}
}
```

Generated TypeScript:
```typescript
export interface Address {
    street: string
    city: string
}

export interface User {
    name: string
    address: Address
}
```

:::tip
Even if you only specify `User::class` in `generate()`, Typographos will automatically discover and include the `Address` class because it's referenced.
:::

