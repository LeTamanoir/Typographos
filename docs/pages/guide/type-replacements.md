# Type Replacements

Type replacements allow you to map PHP types to custom TypeScript types, giving you complete control over how specific types are represented.

## Basic Usage

Use `withTypeReplacement()` to replace any PHP type with a custom TypeScript type:

```php
use Typographos\Generator;

$generator = new Generator();
$generator
    ->withTypeReplacement(\DateTime::class, 'string')
    ->withTypeReplacement('int', 'bigint')
    ->generate([User::class]);
```

## Common Use Cases

### Date/Time Objects

PHP date objects are commonly serialized as strings in JSON APIs:

```php
#[TypeScript]
class Event
{
    public function __construct(
        public string $name,
        public \DateTime $createdAt,           // Will become 'string'
        public \DateTimeImmutable $updatedAt,  // Will become 'string'
    ) {}
}
```

With type replacements:
```php
$generator
    ->withTypeReplacement(\DateTime::class, 'string')
    ->withTypeReplacement(\DateTimeImmutable::class, 'string');
```

Generated TypeScript:
```typescript
export interface Event {
    name: string
    createdAt: string      // Instead of DateTime
    updatedAt: string      // Instead of DateTimeImmutable
}
```

### Primitive Type Modifications

Change how primitive types are represented:

```php
$generator
    ->withTypeReplacement('int', 'bigint')      // For large integers
    ->withTypeReplacement('float', 'string')    // For precise decimals
    ->withTypeReplacement('mixed', 'unknown');  // Better TypeScript type
```

## Custom TypeScript Types

Replace PHP types with your own custom TypeScript types:

```php
// Replace with branded types
$generator
    ->withTypeReplacement('string', 'UserId')
    ->withTypeReplacement('int', 'Timestamp');

// Replace with utility types
$generator
    ->withTypeReplacement(\stdClass::class, 'Record<string, unknown>')
    ->withTypeReplacement('mixed', 'JsonValue');
```

Example:

```php
#[TypeScript]
class User
{
    public function __construct(
        public string $id,        // Becomes UserId
        public int $createdAt,    // Becomes Timestamp
        public \stdClass $meta,   // Becomes Record<string, unknown>
    ) {}
}
```

Generated TypeScript:
```typescript
export interface User {
    id: UserId
    createdAt: Timestamp
    meta: Record<string, unknown>
}
```

## Advanced Replacements

### Union Types

Replace with union types:

```php
$generator->withTypeReplacement('mixed', 'string | number | boolean | null');
```

### Generic Types

Use generic TypeScript types:

```php
$generator->withTypeReplacement(\Illuminate\Support\Collection::class, 'Array<T>');
```

## Replacement Rules

### Fully Qualified Class Names

Always use fully qualified class names (FQCN) for PHP classes:

```php
// ✅ Good - Fully qualified
$generator->withTypeReplacement(\App\Models\User::class, 'UserDto');

// ❌ Bad - Relative name
$generator->withTypeReplacement('User', 'UserDto');  // Won't match App\Models\User
```

### Primitive Types

Use lowercase names for PHP primitive types:

```php
// ✅ Good
$generator->withTypeReplacement('int', 'bigint');
$generator->withTypeReplacement('string', 'Text');
$generator->withTypeReplacement('bool', 'boolean');

// ❌ Bad
$generator->withTypeReplacement('Integer', 'bigint');  // Won't match
```

### Replacement Priority

Type replacements are checked in the order they were added:

```php
$generator
    ->withTypeReplacement('string', 'Text')           // First
    ->withTypeReplacement(\DateTime::class, 'string') // Second - won't become 'Text'
    ->withTypeReplacement('string', 'Varchar');       // Overwrites first replacement
```

The last replacement wins for the same type.

## Practical Examples

### API Response Wrapper

```php
$generator->withTypeReplacement('mixed', 'T');  // Generic data

#[TypeScript]
class ApiResponse
{
    public function __construct(
        public bool $success,
        public mixed $data,        // Becomes T
        public ?string $message = null,
    ) {}
}
```

### Money and Currency

```php
$generator->withTypeReplacement(\Money::class, 'string');

#[TypeScript]
class Product
{
    public function __construct(
        public string $name,
        public \Money $price,      // Becomes string
    ) {}
}
```

### File Uploads

```php
$generator->withTypeReplacement(\Psr\Http\Message\UploadedFileInterface::class, 'File');
```

## Limitations

### Self and Parent References

Type replacements don't affect `self` and `parent` references:

```php
#[TypeScript]
class User
{
    public function __construct(
        public self $supervisor,  // Always becomes 'User', not replacement
    ) {}
}

$generator->withTypeReplacement(User::class, 'UserDto');  // Won't affect 'self'
```

### Inline Types

Type replacements don't apply to inline types - the actual structure is always inlined.

## Best Practices

1. **Document replacements** - Keep a record of your type replacement strategy
2. **Use consistent mappings** - Apply the same replacements across all generators
3. **Test replacements** - Verify that replaced types work with your frontend code

```php
// Good - Document your strategy
$generator
    // API serialization: dates as ISO strings
    ->withTypeReplacement(\DateTime::class, 'string')
    ->withTypeReplacement(\DateTimeImmutable::class, 'string')

    // Money as formatted strings like "10.00 USD"
    ->withTypeReplacement(\Money::class, 'string')

    // Files as browser File objects for uploads
    ->withTypeReplacement(\UploadedFile::class, 'File');
```