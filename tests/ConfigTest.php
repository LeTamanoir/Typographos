<?php

declare(strict_types=1);

use Typographos\Generator;

it('has sensible defaults', function (): void {
    $generator = new Generator;

    expect($generator->indent)->toBe("\t");
    expect($generator->typeReplacements)->toBe([]);
    expect($generator->discoverDirectories)->toBe([]);
    expect($generator->filePath)->toBe('generated.d.ts');
});

it('can set custom values using fluent interface', function (): void {
    $generator = new Generator()
        ->withIndent('  ')
        ->withTypeReplacement('int', 'number')
        ->withTypeReplacement(\DateTime::class, 'string')
        ->withDiscoverFrom('/some/path')
        ->outputTo('custom.d.ts');

    expect($generator->indent)->toBe('  ');
    expect($generator->typeReplacements)->toBe([
        'int' => 'number',
        \DateTime::class => 'string',
    ]);
    expect($generator->discoverDirectories)->toBe(['/some/path']);
    expect($generator->filePath)->toBe('custom.d.ts');
});
