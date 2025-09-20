<?php

declare(strict_types=1);

use Typographos\Generator;
use Typographos\Tests\Fixtures\WithEnums;
use Typographos\Tests\Fixtures\StringEnum;
use Typographos\Tests\Fixtures\IntEnum;

afterEach(function (): void {
    if (file_exists('tests/enum-literals-generated.d.ts')) {
        unlink('tests/enum-literals-generated.d.ts');
    }
});

it('can generate enums', function (): void {
    new Generator()
        ->withOutputPath('tests/enum-literals-generated.d.ts')
        ->withIndent('    ')
        ->generate([WithEnums::class, StringEnum::class, IntEnum::class]);

    expect(file_get_contents('tests/enum-literals-generated.d.ts'))
        ->toBe(file_get_contents('tests/Expected/enum-literals.d.ts'));
});