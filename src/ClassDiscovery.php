<?php

declare(strict_types=1);

namespace Typographos;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RuntimeException;
use SplFileInfo;
use Typographos\Attributes\TypeScript;

final class ClassDiscovery
{
    /**
     * Scan a directory for classes with TypeScript attribute using Composer's class map
     *
     * This method safely discovers classes by using Composer's autoloader class map
     * instead of requiring files directly, avoiding potential code execution issues.
     *
     * @param  string[]  $directories
     * @return class-string[]
     */
    public static function discover(string $composerClassMapPath, array $directories): array
    {
        $classes = [];

        $composerClassMap = self::getComposerClassMap($composerClassMapPath);

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                throw new RuntimeException('Auto discover directory not found: ' . $dir);
            }

            $realDir = realpath($dir);
            if ($realDir === false) {
                throw new RuntimeException('Cannot resolve real path for directory: ' . $dir);
            }

            foreach ($composerClassMap as $class => $file) {
                $realFile = realpath($file);
                if ($realFile && str_starts_with($realFile, $realDir)) {
                    if (class_exists($class) || interface_exists($class) || enum_exists($class)) {
                        $reflection = new ReflectionClass($class);

                        if (count($reflection->getAttributes(TypeScript::class)) > 0) {
                            $classes[] = $class;
                        }
                    }
                }
            }
        }

        return $classes;
    }

    private static function getComposerClassMap(string $composerClassMapPath): array
    {
        if (file_exists($composerClassMapPath)) {
            $classMap = require $composerClassMapPath;
            if (is_array($classMap)) {
                return $classMap;
            }
        }
        throw new RuntimeException('Cannot load Composer class map from ' . $composerClassMapPath);
    }
}
