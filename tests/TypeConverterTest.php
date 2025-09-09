<?php

declare(strict_types=1);

// Test class for user-defined type testing
class UserDefinedTestClass
{
    public function __construct(
        public string $name,
    ) {}
}

use Typographos\Dto\GenCtx;
use Typographos\Dto\RawType;
use Typographos\Dto\ReferenceType;
use Typographos\Dto\RenderCtx;
use Typographos\Dto\ScalarType;
use Typographos\Dto\UnionType;
use Typographos\Enums\EnumStyle;
use Typographos\Enums\RecordStyle;
use Typographos\Queue;
use Typographos\TypeConverter;

$renderCtx = new RenderCtx('', 0, EnumStyle::ENUMS, RecordStyle::INTERFACES);

it('handles empty string type', function () use ($renderCtx): void {
    $ctx = new GenCtx(new Queue([]), [], null);
    $result = TypeConverter::convert($ctx, '');

    expect($result)->toBeInstanceOf(ScalarType::class);
    expect($result->render($renderCtx))->toBe('unknown');
});

it('handles nullable type replacements', function () use ($renderCtx): void {
    $typeReplacements = ['CustomType' => 'MyCustomType'];
    $ctx = new GenCtx(new Queue([]), $typeReplacements, null);

    $result = TypeConverter::convert($ctx, '?CustomType');

    expect($result)->toBeInstanceOf(UnionType::class);
    expect($result->render($renderCtx))->toBe('MyCustomType | null');
});

it('handles basic type replacements', function () use ($renderCtx): void {
    $typeReplacements = [
        'int' => 'number',
        'string' => 'text',
    ];
    $ctx = new GenCtx(new Queue([]), $typeReplacements, null);

    $result = TypeConverter::convert($ctx, 'int');
    expect($result)->toBeInstanceOf(RawType::class);
    expect($result->render($renderCtx))->toBe('number');

    $result2 = TypeConverter::convert($ctx, 'string');
    expect($result2)->toBeInstanceOf(RawType::class);
    expect($result2->render($renderCtx))->toBe('text');
});

it('handles scalar types', function () use ($renderCtx): void {
    $ctx = new GenCtx(new Queue([]), [], null);

    $result = TypeConverter::convert($ctx, 'string');
    expect($result)->toBeInstanceOf(ScalarType::class);
    expect($result->render($renderCtx))->toBe('string');

    $result2 = TypeConverter::convert($ctx, 'int');
    expect($result2)->toBeInstanceOf(ScalarType::class);
    expect($result2->render($renderCtx))->toBe('number');
});

it('handles nullable scalar types', function () use ($renderCtx): void {
    $ctx = new GenCtx(new Queue([]), [], null);

    $result = TypeConverter::convert($ctx, '?string');
    expect($result)->toBeInstanceOf(UnionType::class);
    expect($result->render($renderCtx))->toBe('string | null');
});

it('handles union types', function () use ($renderCtx): void {
    $ctx = new GenCtx(new Queue([]), [], null);

    $result = TypeConverter::convert($ctx, 'string|int|bool');
    expect($result)->toBeInstanceOf(UnionType::class);
    expect($result->render($renderCtx))->toBe('string | number | boolean');
});

it('handles user-defined classes', function () use ($renderCtx): void {
    $ctx = new GenCtx(new Queue([]), [], null);

    $result = TypeConverter::convert($ctx, 'UserDefinedTestClass');
    expect($result)->toBeInstanceOf(ReferenceType::class);
    expect($result->render($renderCtx))->toBe('UserDefinedTestClass');
});

it('handles unknown types', function () use ($renderCtx): void {
    $ctx = new GenCtx(new Queue([]), [], null);

    $result = TypeConverter::convert($ctx, 'NonExistentClass');
    expect($result)->toBeInstanceOf(ScalarType::class);
    expect($result->render($renderCtx))->toBe('unknown');
});

it('handles mixed null types correctly', function () use ($renderCtx): void {
    $ctx = new GenCtx(new Queue([]), [], null);

    // null and mixed shouldn't get extra null union
    $result1 = TypeConverter::convert($ctx, '?null');
    expect($result1->render($renderCtx))->toBe('null');

    $result2 = TypeConverter::convert($ctx, '?mixed');
    expect($result2->render($renderCtx))->toBe('any');
});

it('enqueues user-defined classes', function () use ($renderCtx): void {
    $queue = new Queue([]);
    $ctx = new GenCtx($queue, [], null);

    TypeConverter::convert($ctx, 'UserDefinedTestClass');

    // The queue should now contain UserDefinedTestClass
    $queuedClass = $queue->shift();
    expect($queuedClass)->toBe('UserDefinedTestClass');
});

it('handles complex type replacements with nullability', function () use ($renderCtx): void {
    $typeReplacements = [
        'CustomInterface' => 'MyInterface',
        'AnotherType' => 'SomeType',
    ];
    $ctx = new GenCtx(new Queue([]), $typeReplacements, null);

    $result1 = TypeConverter::convert($ctx, '?CustomInterface');
    expect($result1->render($renderCtx))->toBe('MyInterface | null');

    $result2 = TypeConverter::convert($ctx, '?AnotherType');
    expect($result2->render($renderCtx))->toBe('SomeType | null');
});
