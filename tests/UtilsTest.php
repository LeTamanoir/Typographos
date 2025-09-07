<?php

declare(strict_types=1);

use Typographos\Utils;

it('returns property name as-is for valid identifiers', function (): void {
    expect(Utils::tsProp('validName'))->toBe('validName');
    expect(Utils::tsProp('valid_name'))->toBe('valid_name');
    expect(Utils::tsProp('valid123'))->toBe('valid123');
    expect(Utils::tsProp('$valid'))->toBe('$valid');
    expect(Utils::tsProp('_valid'))->toBe('_valid');
});

it('quotes and escapes invalid identifiers', function (): void {
    expect(Utils::tsProp('invalid-name'))->toBe('"invalid-name"');
    expect(Utils::tsProp('123invalid'))->toBe('"123invalid"');
    expect(Utils::tsProp('invalid name'))->toBe('"invalid name"');
    expect(Utils::tsProp('invalid.name'))->toBe('"invalid.name"');
});

it('properly escapes quotes and backslashes in property names', function (): void {
    expect(Utils::tsProp('name"with"quotes'))->toBe('"name\\"with\\"quotes"');
    expect(Utils::tsProp('name\\with\\backslashes'))->toBe('"name\\\\with\\\\backslashes"');
    expect(Utils::tsProp('complex"name\\with"both'))->toBe('"complex\\"name\\\\with\\"both"');
});

it('handles empty property name', function (): void {
    expect(Utils::tsProp(''))->toBe('""');
});

it('handles special characters', function (): void {
    expect(Utils::tsProp('@special'))->toBe('"@special"');
    expect(Utils::tsProp('#hash'))->toBe('"#hash"');
    expect(Utils::tsProp('percent%'))->toBe('"percent%"');
});
