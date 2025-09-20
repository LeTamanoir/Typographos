<?php

declare(strict_types=1);

use Typographos\Enums\RecordStyle;
use Typographos\Generator;
use Typographos\Tests\Fixtures\SimpleRecord;

afterEach(function (): void {
    if (file_exists('tests/record-types-generated.d.ts')) {
        unlink('tests/record-types-generated.d.ts');
    }
});

it('can generate records as types', function (): void {
    new Generator()
        ->withOutputPath('tests/record-types-generated.d.ts')
        ->withIndent('    ')
        ->withRecordsStyle(RecordStyle::TYPES)
        ->generate([SimpleRecord::class]);

    expect(file_get_contents('tests/record-types-generated.d.ts'))
        ->toBe(file_get_contents('tests/Expected/record-types.d.ts'));
});