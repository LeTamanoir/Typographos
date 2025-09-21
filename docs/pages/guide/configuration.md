# Configuration

Typographos offers extensive configuration options to customize the generated TypeScript output.

## Generator Options

### Output Styles

Control how different PHP constructs are converted to TypeScript:

```php
use Typographos\Generator;
use Typographos\Enums\RecordStyle;
use Typographos\Enums\EnumStyle;

$generator = new Generator();
$generator
    ->withRecordsStyle(RecordStyle::TYPES)      // Use 'type' instead of 'interface'
    ->withEnumsStyle(EnumStyle::TYPES)          // Use union types instead of enums
    ->generate([User::class]);
```

#### Record Styles

**`RecordStyle::INTERFACES` (default):**
```typescript
export interface User {
    name: string
    age: number
}
```

**`RecordStyle::TYPES`:**
```typescript
export type User = {
    name: string
    age: number
}
```

#### Enum Styles

**`EnumStyle::ENUMS` (default):**
```typescript
export enum Status {
    PENDING = "pending",
    ACTIVE = "active",
}
```

**`EnumStyle::TYPES`:**
```typescript
export type Status = "pending" | "active"
```

### Indentation

Customize the indentation style of the generated TypeScript:

```php
$generator = new Generator();

// Use tabs (default)
$generator->withIndent("\t");

// Use 2 spaces
$generator->withIndent("  ");

// Use 4 spaces
$generator->withIndent("    ");
```

### Type Replacements

Replace PHP types with custom TypeScript types:

```php
$generator = new Generator();
$generator
    ->withTypeReplacement(\DateTime::class, 'string')
    ->withTypeReplacement(\DateTimeImmutable::class, 'Date')
    ->withTypeReplacement('int', 'bigint')
    ->generate([User::class]);
```

This is particularly useful for:
- Converting PHP objects to primitive types
- Using custom TypeScript types
- Handling framework-specific types

Example with replacements:

```php
#[TypeScript]
class Event
{
    public function __construct(
        public string $name,
        public \DateTime $createdAt,    // Becomes 'string'
        public int $timestamp,          // Becomes 'bigint'
    ) {}
}
```

Generated TypeScript:
```typescript
export interface Event {
    name: string
    createdAt: string     // Instead of DateTime
    timestamp: bigint     // Instead of number
}
```

## Discovery Options

### Directory Scanning

Auto-discover classes from multiple directories:

```php
$generator = new Generator();
$generator
    ->withDiscovery('src/DTO')
    ->withDiscovery('app/Models')
    ->withDiscovery('domain/ValueObjects')
    ->generate();
```

### Recursive Scanning

Discovery automatically scans subdirectories recursively. To find all classes in a project:

```php
$generator = new Generator();
$generator
    ->withDiscovery('src')  // Scans all subdirectories
    ->generate();
```

## Output Options

### File Output

Write generated types directly to a file:

```php
$generator = new Generator();
$generator
    ->withOutputPath('resources/js/types.d.ts')
    ->generate([User::class]);
```

### String Output

Get the generated TypeScript as a string without writing to file:

```php
$generator = new Generator();
$typeScript = $generator->generate([User::class]);

// Process the string as needed
file_put_contents('custom-location.d.ts', $typeScript);
echo $typeScript;
```

## Advanced Configuration

### Multiple Generators

Use different configurations for different parts of your application:

```php
// API DTOs with interfaces
$apiGenerator = new Generator();
$apiGenerator
    ->withRecordsStyle(RecordStyle::INTERFACES)
    ->withDiscovery('src/API/DTO')
    ->withOutputPath('frontend/src/types/api.d.ts')
    ->generate();

// Domain models with types
$domainGenerator = new Generator();
$domainGenerator
    ->withRecordsStyle(RecordStyle::TYPES)
    ->withEnumsStyle(EnumStyle::TYPES)
    ->withDiscovery('src/Domain')
    ->withOutputPath('frontend/src/types/domain.d.ts')
    ->generate();
```

### Framework Integration

#### Laravel

```php
// In a Laravel command or service provider
$generator = new Generator();
$generator
    ->withDiscovery(app_path('DTO'))
    ->withDiscovery(app_path('Models'))
    ->withOutputPath(resource_path('js/types.d.ts'))
    ->withTypeReplacement(\Illuminate\Support\Carbon::class, 'string')
    ->generate();
```

#### Symfony

```php
// In a Symfony command
$generator = new Generator();
$generator
    ->withDiscovery($this->getParameter('kernel.project_dir') . '/src/DTO')
    ->withOutputPath($this->getParameter('kernel.project_dir') . '/assets/types.d.ts')
    ->withTypeReplacement(\DateTimeInterface::class, 'string')
    ->generate();
```

## Error Handling

Typographos provides clear error messages for common issues:

```php
try {
    $generator = new Generator();
    $generator->generate([]);
} catch (\Exception $e) {
    echo $e->getMessage(); // "No classes to generate"
}
```

Common exceptions:
- **"No classes to generate"** - Call `generate()` with classes or use `withDiscovery()`
- **"Missing doc comment for array property"** - Add PHPDoc `@var` for array properties
- **"Intersection types are not supported"** - Use union types instead

## Performance Tips

1. **Use discovery sparingly** - Only scan directories that contain TypeScript-annotated classes
2. **Specify classes explicitly** - When possible, pass specific class names instead of using discovery
3. **Cache generated types** - Only regenerate when PHP classes change

```php
// Good: Specific classes
$generator->generate([User::class, Post::class]);

// Avoid: Scanning entire src directory if most classes don't have #[TypeScript]
$generator->withDiscovery('src')->generate();
```