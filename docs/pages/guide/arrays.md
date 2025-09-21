# Arrays & PHPDoc

Typographos provides rich support for PHP arrays and integrates seamlessly with PHPDoc annotations to generate precise TypeScript array types.

## The Array Challenge

PHP's `array` type is ambiguous - it could represent a list, a map, or mixed data. To generate accurate TypeScript types, Typographos requires PHPDoc annotations to understand the array structure.

## PHPDoc Array Types

Use PHPDoc `@var` comments to specify array types:

```php
#[TypeScript]
class User
{
    public function __construct(
        /** @var list<string> */
        public array $roles,

        /** @var array<string, int> */
        public array $scoresByCategory,

        /** @var non-empty-list<User> */
        public array $friends,
    ) {}
}
```

Generated TypeScript:
```typescript
export interface User {
    roles: string[]
    scoresByCategory: Record<string, number>
    friends: User[]  // Non-empty constraint is not enforced in TS
}
```

## Supported Array Formats

### Lists (Indexed Arrays)

**`list<T>`** - Standard indexed array:
```php
/** @var list<string> */
public array $tags;  // string[]
```

**`non-empty-list<T>`** - Non-empty indexed array:
```php
/** @var non-empty-list<int> */
public array $ids;   // number[] (non-empty constraint noted but not enforced)
```

### Maps (Associative Arrays)

**`array<K,V>`** - Key-value pairs:
```php
/** @var array<string, int> */
public array $counts;           // Record<string, number>

/** @var array<int, string> */
public array $labels;           // Record<number, string>

/** @var array<array-key, mixed> */
public array $data;             // Record<string | number, unknown>
```

### Nested Arrays

Complex nested structures are fully supported:

```php
#[TypeScript]
class Dashboard
{
    public function __construct(
        /** @var list<list<string>> */
        public array $matrix,              // string[][]

        /** @var array<string, list<int>> */
        public array $dataPoints,          // Record<string, number[]>

        /** @var list<array<string, mixed>> */
        public array $records,             // Record<string, unknown>[]
    ) {}
}
```

## Constructor Parameter PHPDoc

PHPDoc can be placed on constructor parameters instead of properties:

```php
#[TypeScript]
class Product
{
    public function __construct(
        public string $name,

        /** @param list<string> $categories */
        public array $categories,

        /** @param array<string, float> $prices */
        public array $prices,
    ) {}
}
```

Both `@var` and `@param` work identically for array type detection.

## Key Type Support

Typographos supports various key types for associative arrays:

### String Keys
```php
/** @var array<string, mixed> */
public array $data;              // Record<string, unknown>
```

### Integer Keys
```php
/** @var array<int, string> */
public array $labels;            // Record<number, string>
```

### Array-Key (String or Int)
```php
/** @var array<array-key, User> */
public array $users;             // Record<string | number, User>
```

### Unsupported Key Types

These key types will throw exceptions:
```php
/** @var array<bool, string> */    // ❌ Boolean keys not supported
/** @var array<object, int> */     // ❌ Object keys not supported
/** @var array<User, string> */    // ❌ Class keys not supported
```

## Array Without PHPDoc

Arrays without PHPDoc annotations default to `unknown[]`:

```php
#[TypeScript]
class Data
{
    public function __construct(
        public array $items,        // No PHPDoc
    ) {}
}
```

Generated TypeScript:
```typescript
export interface Data {
    items: unknown[]    // Could be anything
}
```

:::warning
Typographos will throw an exception if it encounters an `array` property without PHPDoc in strict mode. Always provide PHPDoc for array properties.
:::

## Complex Examples

### E-commerce Product

```php
#[TypeScript]
class Product
{
    public function __construct(
        public string $name,

        /** @var list<string> */
        public array $images,

        /** @var array<string, string> */
        public array $attributes,       // color: "red", size: "large"

        /** @var list<array<string, mixed>> */
        public array $variants,         // Each variant is an object

        /** @var array<string, list<float>> */
        public array $priceHistory,     // Currency => price history
    ) {}
}
```

Generated TypeScript:
```typescript
export interface Product {
    name: string
    images: string[]
    attributes: Record<string, string>
    variants: Record<string, unknown>[]
    priceHistory: Record<string, number[]>
}
```

### Analytics Dashboard

```php
#[TypeScript]
class Analytics
{
    public function __construct(
        /** @var array<string, array<string, int>> */
        public array $metrics,          // Nested Record types

        /** @var list<list<list<float>>> */
        public array $timeSeries,       // 3D array of data points

        /** @var non-empty-list<array<array-key, mixed>> */
        public array $reports,          // At least one report required
    ) {}
}
```

Generated TypeScript:
```typescript
export interface Analytics {
    metrics: Record<string, Record<string, number>>
    timeSeries: number[][][]
    reports: Record<string | number, unknown>[]
}
```

## Best Practices

1. **Always document arrays** - Provide PHPDoc for all array properties
2. **Use specific types** - Prefer `list<T>` over `array<int, T>` for indexed arrays
3. **Be consistent** - Use the same PHPDoc format throughout your codebase
4. **Document early** - Add PHPDoc when creating the property, not later

```php
// Good
/** @var list<User> */
public array $users;

// Avoid (but works)
/** @var array<int, User> */
public array $users;

// Bad (will cause issues)
public array $users;  // No PHPDoc
```

## Migration from Generic Arrays

If you have existing code with untyped arrays:

```php
// Before
public array $data;

// After - Add appropriate PHPDoc
/** @var array<string, mixed> */
public array $data;
```

## Error Messages

Common array-related errors and solutions:

- **"Missing doc comment for array property"** - Add `@var` PHPDoc annotation
- **"Unsupported array key type 'bool'"** - Use string, int, or array-key
- **"Invalid PHPDoc array format"** - Check syntax: `list<T>` or `array<K,V>`