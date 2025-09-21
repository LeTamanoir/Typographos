# Inline Types

The `#[Inline]` attribute embeds type definitions directly instead of creating separate TypeScript interfaces.

## Basic Usage

By default, referenced classes generate separate interfaces:

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
        public Address $address,  // Creates reference
    ) {}
}
```

**Default Output:**
```typescript
export interface Address {
    street: string
    city: string
}

export interface User {
    name: string
    address: Address  // Reference to separate interface
}
```

## Using the Inline Attribute

Add `#[Inline]` to embed the type definition directly:

```php
use Typographos\Attributes\Inline;

#[TypeScript]
class User
{
    public function __construct(
        public string $name,

        #[Inline]  // Embed the Address structure directly
        public Address $address,
    ) {}
}
```

**Inline Output:**
```typescript
export interface User {
    name: string
    address: {  // Inlined structure
        street: string
        city: string
    }
}

// No separate Address interface generated unless Address has #[TypeScript] or is referenced elsewhere
```

## When to Use Inline Types

**✅ Good for:**
- Simple value objects (2-4 properties)
- Objects used only once
- Reducing interface clutter
- Enums you want as union types

**❌ Avoid for:**
- Complex objects with many properties
- Objects used in multiple places
- Domain entities

## Inline with Enums and Arrays

`#[Inline]` works with enums to force union types and with arrays:

```php
#[TypeScript]
class Task
{
    public function __construct(
        #[Inline] public Status $status,     // "pending" | "active"

        /** @var list<Address> */
        #[Inline] public array $addresses,   // { street: string, city: string }[]
    ) {}
}
```

## Key Point: Inline Creates Repetition

The main tradeoff with `#[Inline]` is **repetition vs references**:

```php
// This creates the Address structure 3 times in the output
#[TypeScript]
class Company
{
    public function __construct(
        #[Inline] public Address $headquarters,
        #[Inline] public Address $warehouse,
        #[Inline] public Address $office,
    ) {}
}

// Better: Use references to avoid repetition
#[TypeScript]
class Company
{
    public function __construct(
        public Address $headquarters,  // Shared reference
        public Address $warehouse,     // Shared reference
        public Address $office,        // Shared reference
    ) {}
}
```

**Use inline for one-off structures, references for reusable types.**