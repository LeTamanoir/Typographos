<?php

declare(strict_types=1);

use Typographos\Tests\Fixtures\Child;
use Typographos\Tests\Fixtures\References;
use Typographos\TypeResolver;

it('resolves parent type correctly for class with parent', function (): void {
    $ref = new ReflectionClass(Child::class);
    $prop = $ref->getProperty('parent');

    $result = TypeResolver::resolve($prop);

    expect($result)->toBe('Typographos\\Tests\\Fixtures\\_Parent');
});

it('resolves self type correctly', function (): void {
    $ref = new ReflectionClass(References::class);
    $prop = $ref->getProperty('selfRef');

    $result = TypeResolver::resolve($prop);

    expect($result)->toBe('Typographos\\Tests\\Fixtures\\References');
});
