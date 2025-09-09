<?php

declare(strict_types=1);

use Typographos\Enums\EnumStyle;
use Typographos\Generator;
use Typographos\Tests\Fixtures\WithInlineEnums;

afterEach(function (): void {
    if (file_exists('tests/inline-enums-generated.d.ts')) {
        unlink('tests/inline-enums-generated.d.ts');
    }
});

it('can generate inline enums', function (): void {
    new Generator()
        ->outputTo('tests/inline-enums-generated.d.ts')
        ->withIndent('    ')
        ->withEnumsStyle(EnumStyle::TYPES)
        ->generate([WithInlineEnums::class]);

    expect(file_get_contents('tests/inline-enums-generated.d.ts'))
        ->toBe(file_get_contents('tests/Expected/inline-enums.d.ts'));
});