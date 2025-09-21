# Enums API Reference

Typographos provides enums to control how different PHP constructs are converted to TypeScript.

## RecordStyle Enum

Controls how PHP classes are converted to TypeScript.

```php
use Typographos\Enums\RecordStyle;
```

### Values

#### `RecordStyle::INTERFACES` (default)

Generates TypeScript interfaces:

```typescript
export interface User {
    name: string
    age: number
}
```

**Usage:**
```php
$generator->withRecordsStyle(RecordStyle::INTERFACES);
```

#### `RecordStyle::TYPES`

Generates TypeScript type aliases:

```typescript
export type User = {
    name: string
    age: number
}
```

**Usage:**
```php
$generator->withRecordsStyle(RecordStyle::TYPES);
```

### When to Use Each Style

**Use `INTERFACES` when:**
- You need declaration merging capabilities
- Working with object-oriented TypeScript code
- Building libraries or APIs where interfaces provide better extensibility
- Default choice for most applications

**Use `TYPES` when:**
- You prefer functional programming style
- Need union types or conditional types
- Building JSON schemas or serialization types
- Want more flexible type manipulation

## EnumStyle Enum

Controls how PHP backed enums are converted to TypeScript.

```php
use Typographos\Enums\EnumStyle;
```

### Values

#### `EnumStyle::ENUMS` (default)

Generates TypeScript enums:

```typescript
export enum Status {
    PENDING = "pending",
    ACTIVE = "active",
    INACTIVE = "inactive",
}
```

**Usage:**
```php
$generator->withEnumsStyle(EnumStyle::ENUMS);
```

**Benefits:**
- Reverse lookup capabilities: `Status[Status.PENDING]`
- Strong typing and IDE autocompletion
- Runtime enum object available in JavaScript

#### `EnumStyle::TYPES`

Generates union types:

```typescript
export type Status = "pending" | "active" | "inactive"
```

**Usage:**
```php
$generator->withEnumsStyle(EnumStyle::TYPES);
```

**Benefits:**
- Better JSON serialization
- Smaller bundle size
- More functional programming friendly
- Better tree-shaking in bundlers

### When to Use Each Style

**Use `ENUMS` when:**
- You need reverse lookups
- Working with existing TypeScript code that uses enums
- Want stronger typing and better IDE support
- Building desktop or server applications

**Use `TYPES` when:**
- Building JSON APIs (better serialization)
- Prefer functional programming style
- Need smaller bundle sizes
- Building web applications with strict bundle size requirements

## Usage Examples

### Mixed Styles

You can use different styles for different generators:

```php
// API types - use union types for better JSON handling
$apiGenerator = new Generator();
$apiGenerator
    ->withRecordsStyle(RecordStyle::TYPES)
    ->withEnumsStyle(EnumStyle::TYPES)
    ->withDiscovery('src/API/DTO')
    ->withOutputPath('frontend/api-types.d.ts')
    ->generate();

// Form types - use interfaces and enums for better IDE support
$formGenerator = new Generator();
$formGenerator
    ->withRecordsStyle(RecordStyle::INTERFACES)
    ->withEnumsStyle(EnumStyle::ENUMS)
    ->withDiscovery('src/Forms')
    ->withOutputPath('frontend/form-types.d.ts')
    ->generate();
```

### Configuration Based on Environment

```php
$isDevelopment = $_ENV['APP_ENV'] === 'development';

$generator = new Generator();
$generator
    ->withRecordsStyle($isDevelopment ? RecordStyle::INTERFACES : RecordStyle::TYPES)
    ->withEnumsStyle($isDevelopment ? EnumStyle::ENUMS : EnumStyle::TYPES)
    ->generate([User::class]);
```

### Overriding Global Settings with Inline

The `#[Inline]` attribute overrides global enum style settings:

```php
use Typographos\Enums\EnumStyle;
use Typographos\Attributes\Inline;

// Global setting: use enums
$generator->withEnumsStyle(EnumStyle::ENUMS);

#[TypeScript]
class Task
{
    public function __construct(
        public string $title,

        #[Inline]  // This will be a union type despite global setting
        public Status $status,

        public Priority $priority,  // This will be an enum (global setting)
    ) {}
}
```

Generated TypeScript:
```typescript
export enum Priority {
    LOW = 1,
    MEDIUM = 2,
    HIGH = 3,
}

export interface Task {
    title: string
    status: "pending" | "active" | "inactive"  // Union (inline override)
    priority: Priority  // Enum (global setting)
}
```

## Import Statements

```php
<?php

use Typographos\Enums\RecordStyle;
use Typographos\Enums\EnumStyle;
use Typographos\Generator;

$generator = new Generator();
$generator
    ->withRecordsStyle(RecordStyle::TYPES)
    ->withEnumsStyle(EnumStyle::TYPES)
    ->generate([User::class]);
```

## Default Values

- `RecordStyle::INTERFACES` - Default record style
- `EnumStyle::ENUMS` - Default enum style

These defaults provide the most compatibility with existing TypeScript codebases and offer the best IDE support.