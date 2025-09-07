<?php

declare(strict_types=1);

use Typographos\Generator;
use Typographos\Tests\Fixtures\LiteralTypes;

afterEach(function (): void {
    if (file_exists('tests/literal-types-generated.d.ts')) {
        unlink('tests/literal-types-generated.d.ts');
    }
});

it('can generate literal types', function (): void {
    new Generator()
        ->outputTo('tests/literal-types-generated.d.ts')
        ->withIndent('    ')
        ->generate([LiteralTypes::class]);

    expect(file_get_contents('tests/literal-types-generated.d.ts'))
        ->toBe(file_get_contents('tests/Expected/literal-types.d.ts'));
});