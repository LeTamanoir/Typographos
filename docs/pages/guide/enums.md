# PHP Enums

Typographos supports PHP backed enums, converting them to either TypeScript enums or union types.

## Basic Enum Support

PHP backed enums are automatically detected and converted:

```php
use Typographos\Attributes\TypeScript;

#[TypeScript]
enum Status: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}

#[TypeScript]
enum Priority: int
{
    case LOW = 1;
    case MEDIUM = 2;
    case HIGH = 3;
}
```

## Output Styles

### TypeScript Enums (Default)

```php
use Typographos\Enums\EnumStyle;

$generator->withEnumsStyle(EnumStyle::ENUMS); // Default
```

**Generated:**
```typescript
export enum Status {
    PENDING = "pending",
    ACTIVE = "active",
    INACTIVE = "inactive",
}

export enum Priority {
    LOW = 1,
    MEDIUM = 2,
    HIGH = 3,
}
```

### Union Types

```php
$generator->withEnumsStyle(EnumStyle::TYPES);
```

**Generated:**
```typescript
export type Status = "pending" | "active" | "inactive"
export type Priority = 1 | 2 | 3
```

## Using Enums in Classes

```php
#[TypeScript]
class Task
{
    public function __construct(
        public string $title,
        public Status $status,
        public Priority $priority,

        #[Inline]  // Force union type regardless of global style
        public Status $inlineStatus,
    ) {}
}
```

**Generated (with EnumStyle::ENUMS):**
```typescript
export interface Task {
    title: string
    status: Status
    priority: Priority
    inlineStatus: "pending" | "active" | "inactive"  // Inlined
}
```

## Enums in Arrays and Namespaces

Enums work with arrays and respect PHP namespaces:

```php
#[TypeScript]
class User
{
    public function __construct(
        /** @var list<Status> */
        public array $statuses,           // Status[]

        /** @var array<string, Priority> */
        public array $priorityMap,        // Record<string, Priority>
    ) {}
}
```

```php
namespace App\Enums;

#[TypeScript]
enum Status: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
```

**Generated:**
```typescript
declare namespace App {
    export namespace Enums {
        export enum Status {
            ACTIVE = "active",
            INACTIVE = "inactive",
        }
    }
}
```

## Requirements and Limitations

**✅ Supported:**
- Backed enums with `string` or `int` values
- String enums (recommended for readability)
- Integer enums

**❌ Not supported:**
- Pure enums without backing values
- Enum methods (won't appear in TypeScript)

```php
// ❌ Not supported - no backing type
enum Status
{
    case PENDING;
    case ACTIVE;
}

// ✅ Supported - string backing type
enum Status: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
}
```

## Choosing Enum Styles

**Use TypeScript Enums when:**
- You need reverse lookups
- Want stronger typing and IDE support
- Working with existing enum-based TypeScript

**Use Union Types when:**
- Building JSON APIs (better serialization)
- Want smaller bundle sizes
- Prefer functional programming style
- Need better tree-shaking