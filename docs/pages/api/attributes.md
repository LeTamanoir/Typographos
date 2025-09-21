# Attributes API Reference

Typographos uses PHP attributes to control type generation behavior.

## TypeScript Attribute

### `#[TypeScript]`

Marks a PHP class or enum for TypeScript type generation.

```php
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

**Usage:**
- Must be applied to classes or enums
- Only classes/enums with this attribute are discovered during auto-discovery
- No parameters required

### Supported Constructs

#### Classes
```php
#[TypeScript]
class User { /* ... */ }
```

#### Enums (Backed Only)
```php
#[TypeScript]
enum Status: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
}
```

#### Not Supported
- Interfaces (use classes instead)
- Traits
- Pure enums (without backing type)
- Functions or constants

## Inline Attribute

### `#[Inline]`

Forces a type to be inlined instead of creating a reference.

```php
use Typographos\Attributes\Inline;
use Typographos\Attributes\TypeScript;

#[TypeScript]
class User
{
    public function __construct(
        public string $name,

        #[Inline]  // Embed Address structure directly
        public Address $address,
    ) {}
}
```

**Usage:**
- Applied to class properties
- Works with class types and enum types
- Overrides global enum/record style settings

**Generated Output:**

**Without `#[Inline]`:**
```typescript
export interface User {
    name: string
    address: Address  // Reference
}

export interface Address {
    street: string
    city: string
}
```

**With `#[Inline]`:**
```typescript
export interface User {
    name: string
    address: {        // Inlined structure
        street: string
        city: string
    }
}

export interface Address {  // Still generated for other references
    street: string
    city: string
}
```

### Inline with Enums

```php
#[TypeScript]
enum Status: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
}

#[TypeScript]
class Task
{
    public function __construct(
        public string $title,

        #[Inline]  // Force union type regardless of global enum style
        public Status $status,
    ) {}
}
```

**Output (even with `EnumStyle::ENUMS`):**
```typescript
export enum Status {
    PENDING = "pending",
    ACTIVE = "active",
}

export interface Task {
    title: string
    status: "pending" | "active"  // Inlined as union type
}
```

### Inline with Arrays

```php
#[TypeScript]
class User
{
    public function __construct(
        public string $name,

        /** @var list<Address> */
        #[Inline]  // Inline each Address in the array
        public array $addresses,
    ) {}
}
```

**Generated Output:**
```typescript
export interface User {
    name: string
    addresses: {
        street: string
        city: string
    }[]  // Array of inlined Address structures
}
```

## Attribute Positioning

### Property Attributes

Attributes on properties control how that specific property is handled:

```php
#[TypeScript]
class User
{
    public function __construct(
        public string $name,        // Default behavior

        #[Inline]
        public Address $address,    // This property inlined

        public Address $billing,    // This property referenced
    ) {}
}
```

### Constructor Parameter Attributes

Attributes work on constructor parameters with property promotion:

```php
#[TypeScript]
class User
{
    public function __construct(
        public string $name,

        #[Inline]
        public Address $address,  // Attribute applies to the property
    ) {}
}
```

## Attribute Inheritance

Attributes are **not inherited**. Each class must have its own `#[TypeScript]` attribute:

```php
#[TypeScript]
class User
{
    // User implementation
}

// This class needs its own #[TypeScript] attribute
class AdminUser extends User
{
    // AdminUser implementation
}

// To include AdminUser in generation:
#[TypeScript]
class AdminUser extends User
{
    // Now AdminUser will be discovered
}
```

## Error Conditions

### Missing TypeScript Attribute

```php
class User  // No #[TypeScript] attribute
{
    public function __construct(
        public string $name,
    ) {}
}

$generator->generate([User::class]);  // Will throw exception
```

**Error:** Class must have `#[TypeScript]` attribute

**Solution:** Add the attribute:
```php
#[TypeScript]
class User { /* ... */ }
```

### Inline on Unsupported Types

```php
#[TypeScript]
class User
{
    public function __construct(
        #[Inline]
        public string $name,  // Can't inline primitive types
    ) {}
}
```

**Behavior:** `#[Inline]` is ignored for primitive types

### Inline on Array Without PHPDoc

```php
#[TypeScript]
class User
{
    public function __construct(
        #[Inline]
        public array $data,  // No PHPDoc for array
    ) {}
}
```

**Error:** Missing PHPDoc for array property

**Solution:** Add PHPDoc:
```php
/** @var list<Address> */
#[Inline]
public array $addresses,
```

## Best Practices

### Use TypeScript Attribute Sparingly

Only mark classes that you actually need as TypeScript types:

```php
// ✅ Good - Only DTO classes need TypeScript
namespace App\DTO;

#[TypeScript]
class UserCreateRequest { /* ... */ }

#[TypeScript]
class UserResponse { /* ... */ }

// ✅ Good - Don't mark internal classes
namespace App\Services;

class UserService  // No #[TypeScript] - internal class
{
    // Internal service logic
}
```

### Use Inline for Simple Value Objects

```php
// ✅ Good - Simple value object
#[TypeScript]
class Coordinates
{
    public function __construct(
        public float $lat,
        public float $lng,
    ) {}
}

#[TypeScript]
class Location
{
    public function __construct(
        public string $name,

        #[Inline]  // Simple, single-use value object
        public Coordinates $coords,
    ) {}
}
```

### Avoid Inline for Complex Objects

```php
// ❌ Avoid - Complex object with many properties
#[TypeScript]
class User
{
    public function __construct(
        public string $name,

        public Address $address,  // Keep as reference for reuse
    ) {}
}
```

### Consistent Inline Strategy

```php
// ✅ Good - Consistent strategy
#[TypeScript]
class Order
{
    public function __construct(
        // Inline simple value objects
        #[Inline] public Money $total,
        #[Inline] public Coordinates $location,

        // Reference complex entities
        public User $customer,
        public Product $product,
    ) {}
}
```

## Framework Integration

### Validation Attributes

Typographos attributes can coexist with validation attributes:

```php
use Symfony\Component\Validator\Constraints as Assert;
use Typographos\Attributes\TypeScript;

#[TypeScript]
class ContactForm
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,

        #[Assert\NotBlank]
        public string $message,
    ) {}
}
```

Both Symfony validation attributes and Typographos attributes work together on the same class.