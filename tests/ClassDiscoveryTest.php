<?php

declare(strict_types=1);

use Typographos\ClassDiscovery;

it('throws exception for non-existent directory', function (): void {
    // Create a mock class map that would be valid
    $tempClassMapFile = tempnam(sys_get_temp_dir(), 'classmap');
    file_put_contents($tempClassMapFile, '<?php return [];');

    // Use a non-existent directory
    $invalidDir = '/path/that/definitely/does/not/exist/'.uniqid();

    expect(fn () => ClassDiscovery::discover($tempClassMapFile, [$invalidDir]))
        ->toThrow(RuntimeException::class, 'Auto discover directory not found: ' . $invalidDir);

    unlink($tempClassMapFile);
});

it('throws exception when composer class map file does not exist', function (): void {
    $nonExistentFile = '/path/that/does/not/exist/classmap.php';

    expect(fn () => ClassDiscovery::discover($nonExistentFile, [__DIR__]))
        ->toThrow(RuntimeException::class, 'Cannot load Composer class map from ' . $nonExistentFile);
});

it('handles classes that do not exist in class map', function (): void {
    // Create a class map with a non-existent class
    $tempClassMapFile = tempnam(sys_get_temp_dir(), 'classmap');
    $tempDir = sys_get_temp_dir();
    $tempFile = $tempDir . '/NonExistentClass.php';

    // Create the class map pointing to our temp directory
    file_put_contents($tempClassMapFile, '<?php return ["NonExistentClass" => "' . $tempFile . '"];');

    // Create the file but don't define the class in it
    file_put_contents($tempFile, '<?php // Empty file');

    $result = ClassDiscovery::discover($tempClassMapFile, [$tempDir]);

    // Should return empty array since the class doesn't actually exist
    expect($result)->toBe([]);

    // Cleanup
    unlink($tempClassMapFile);
    unlink($tempFile);
});

it('handles classes without TypeScript attribute', function (): void {
    // Create a class map and a real class without TypeScript attribute
    $tempClassMapFile = tempnam(sys_get_temp_dir(), 'classmap');
    $tempDir = sys_get_temp_dir();
    $uniqueId = uniqid();
    $className = 'TestClassWithoutAttribute' . $uniqueId;
    $tempFile = $tempDir . '/' . $className . '.php';

    // Create a class without the TypeScript attribute
    file_put_contents($tempFile, '<?php
class ' . $className . ' {
    public string $property;
}');

    // Create the class map
    file_put_contents($tempClassMapFile, '<?php return ["' . $className . '" => "' . $tempFile . '"];');

    // Include the file to make the class available
    require_once $tempFile;

    $result = ClassDiscovery::discover($tempClassMapFile, [$tempDir]);

    // Should return empty array since the class has no TypeScript attribute
    expect($result)->toBe([]);

    // Cleanup
    unlink($tempClassMapFile);
    unlink($tempFile);
});