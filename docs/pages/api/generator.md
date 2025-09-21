# Generator API Reference

The `Generator` class is the main entry point for Typographos. It provides a fluent interface for configuring and generating TypeScript types from PHP classes.

## Constructor

```php
use Typographos\Generator;

$generator = new Generator();
```

The `Generator` class has no constructor parameters. All configuration is done through method chaining.

## Configuration Methods

### withDiscovery(string $directory)

Enable auto-discovery of classes marked with `#[TypeScript]` from the specified directory.

```php
$generator->withDiscovery('src/DTO');
$generator->withDiscovery('app/Models');  // Multiple calls are additive
```

**Parameters:**
- `$directory` - Path to directory to scan recursively for classes

**Returns:** `Generator` (for method chaining)

### withOutputPath(string $filePath)

Set the output file path for generated TypeScript.

```php
$generator->withOutputPath('types.d.ts');
$generator->withOutputPath('resources/js/api-types.d.ts');
```

**Parameters:**
- `$filePath` - Path where the TypeScript file will be written

**Returns:** `Generator` (for method chaining)

### withIndent(string $indent)

Set the indentation style for generated TypeScript.

```php
$generator->withIndent("\t");      // Tabs (default)
$generator->withIndent("  ");      // 2 spaces
$generator->withIndent("    ");    // 4 spaces
```

**Parameters:**
- `$indent` - String to use for each level of indentation

**Returns:** `Generator` (for method chaining)

**Default:** `"\t"` (tab character)

### withRecordsStyle(RecordStyle $style)

Set how PHP classes are converted to TypeScript.

```php
use Typographos\Enums\RecordStyle;

$generator->withRecordsStyle(RecordStyle::INTERFACES);  // Default
$generator->withRecordsStyle(RecordStyle::TYPES);
```

**Parameters:**
- `$style` - `RecordStyle::INTERFACES` or `RecordStyle::TYPES`

**Returns:** `Generator` (for method chaining)

**Default:** `RecordStyle::INTERFACES`

**Output difference:**
- `INTERFACES`: `export interface User { name: string }`
- `TYPES`: `export type User = { name: string }`

### withEnumsStyle(EnumStyle $style)

Set how PHP enums are converted to TypeScript.

```php
use Typographos\Enums\EnumStyle;

$generator->withEnumsStyle(EnumStyle::ENUMS);   // Default
$generator->withEnumsStyle(EnumStyle::TYPES);
```

**Parameters:**
- `$style` - `EnumStyle::ENUMS` or `EnumStyle::TYPES`

**Returns:** `Generator` (for method chaining)

**Default:** `EnumStyle::ENUMS`

**Output difference:**
- `ENUMS`: `export enum Status { ACTIVE = "active" }`
- `TYPES`: `export type Status = "active"`

### withTypeReplacement(string $phpType, string $tsType)

Replace a PHP type with a custom TypeScript type.

```php
$generator->withTypeReplacement(\DateTime::class, 'string');
$generator->withTypeReplacement('int', 'bigint');
$generator->withTypeReplacement('mixed', 'JsonValue');
```

**Parameters:**
- `$phpType` - The PHP type to replace (FQCN for classes, lowercase for primitives)
- `$tsType` - The TypeScript type to use instead

**Returns:** `Generator` (for method chaining)

**Note:** Multiple calls are additive. Last replacement wins for the same type.

## Generation Method

### generate(array $classNames = [])

Generate TypeScript types and optionally write to file.

```php
// With auto-discovery
$generator
    ->withDiscovery('src/DTO')
    ->withOutputPath('types.d.ts')
    ->generate();

// With explicit classes
$generator
    ->withOutputPath('types.d.ts')
    ->generate([User::class, Post::class]);

// Get as string (no file output)
$typeScript = $generator->generate([User::class]);
```

**Parameters:**
- `$classNames` - Array of fully qualified class names to generate types for. If empty, uses auto-discovery.

**Returns:** `string` - The generated TypeScript code

**Behavior:**
- If `withOutputPath()` was called, writes the TypeScript to that file
- Always returns the generated TypeScript as a string
- If no classes provided and no discovery configured, throws exception
- Automatically discovers dependencies (referenced classes)

## Method Chaining Example

```php
use Typographos\Generator;
use Typographos\Enums\RecordStyle;
use Typographos\Enums\EnumStyle;

$typeScript = (new Generator())
    ->withDiscovery('src/API/DTO')
    ->withDiscovery('src/Domain/ValueObjects')
    ->withOutputPath('frontend/src/types/api.d.ts')
    ->withIndent('  ')
    ->withRecordsStyle(RecordStyle::TYPES)
    ->withEnumsStyle(EnumStyle::TYPES)
    ->withTypeReplacement(\DateTime::class, 'string')
    ->withTypeReplacement(\Money::class, 'string')
    ->generate();

echo $typeScript;  // Also available as string
```

## Exception Handling

The `Generator` class can throw several exceptions:

### No Classes to Generate

```php
try {
    $generator = new Generator();
    $generator->generate();  // No discovery, no classes
} catch (\Exception $e) {
    echo $e->getMessage();  // "No classes to generate"
}
```

**Solution:** Use `withDiscovery()` or pass classes to `generate()`

### Missing PHPDoc for Arrays

```php
#[TypeScript]
class User
{
    public function __construct(
        public array $roles,  // No PHPDoc - will throw
    ) {}
}
```

**Solution:** Add PHPDoc annotation:
```php
/** @var list<string> */
public array $roles;
```

### Intersection Types Not Supported

```php
#[TypeScript]
class User
{
    public function __construct(
        public Serializable&JsonSerializable $data,  // Intersection - will throw
    ) {}
}
```

**Solution:** Use union types or a single interface instead

### File Write Errors

```php
$generator
    ->withOutputPath('/read-only/types.d.ts')  // Permission denied
    ->generate([User::class]);
```

**Solution:** Ensure the output directory is writable

## Performance Considerations

### Discovery Performance

Auto-discovery scans the filesystem recursively:

```php
// Slow - scans entire src directory
$generator->withDiscovery('src');

// Faster - scans only DTO directory
$generator->withDiscovery('src/DTO');

// Fastest - explicit classes (no scanning)
$generator->generate([User::class, Post::class]);
```

### Memory Usage

Large codebases with many classes may use significant memory:

```php
// Process in batches for very large codebases
$batch1 = $generator->generate([User::class, Post::class]);
$batch2 = $generator->generate([Order::class, Product::class]);
```

### Dependency Resolution

The generator automatically finds and includes referenced classes:

```php
// This will include both User and Address classes
$generator->generate([User::class]);  // User references Address
```

## Integration Examples

### Laravel Artisan Command

```php
use Illuminate\Console\Command;
use Typographos\Generator;

class GenerateTypesCommand extends Command
{
    protected $signature = 'types:generate';

    public function handle()
    {
        $generator = new Generator();
        $generator
            ->withDiscovery(app_path('DTO'))
            ->withOutputPath(resource_path('js/types.d.ts'))
            ->withTypeReplacement(\Illuminate\Support\Carbon::class, 'string')
            ->generate();

        $this->info('TypeScript types generated successfully!');
    }
}
```

### Symfony Console Command

```php
use Symfony\Component\Console\Command\Command;
use Typographos\Generator;

class GenerateTypesCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $generator = new Generator();
        $generator
            ->withDiscovery($this->getProjectDir() . '/src/DTO')
            ->withOutputPath($this->getProjectDir() . '/assets/types.d.ts')
            ->withTypeReplacement(\DateTimeInterface::class, 'string')
            ->generate();

        $output->writeln('TypeScript types generated!');
        return Command::SUCCESS;
    }
}
```

### Build Script Integration

```php
// build-types.php
require 'vendor/autoload.php';

use Typographos\Generator;

$generator = new Generator();

try {
    $generator
        ->withDiscovery('src/DTO')
        ->withOutputPath('frontend/types.d.ts')
        ->generate();

    echo "✅ Types generated successfully\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
```