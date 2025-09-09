<?php

declare(strict_types=1);

use Typographos\Generator;
use Typographos\Tests\Fixtures\BracketArrays;

afterEach(function (): void {
    if (file_exists('tests/bracket-arrays-generated.d.ts')) {
        unlink('tests/bracket-arrays-generated.d.ts');
    }
});

it('can generate bracket array syntax', function (): void {
    new Generator()
        ->outputTo('tests/bracket-arrays-generated.d.ts')
        ->withIndent('    ')
        ->generate([BracketArrays::class]);

    expect(file_get_contents('tests/bracket-arrays-generated.d.ts'))
        ->toBe(file_get_contents('tests/Expected/bracket-arrays.d.ts'));
});