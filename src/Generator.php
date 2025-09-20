<?php

declare(strict_types=1);

namespace Typographos;

use InvalidArgumentException;
use RuntimeException;
use Typographos\Context\GenerationContext;
use Typographos\Context\RenderContext;
use Typographos\Enums\EnumStyle;
use Typographos\Enums\RecordStyle;
use Typographos\Interfaces\Type;
use Typographos\Types\EnumType;
use Typographos\Types\RecordType;
use Typographos\Types\RootType;

final class Generator
{
    /**
     * Indentation style
     */
    public string $indent = "\t";

    /**
     * Type replacements to replace PHP types with TypeScript types
     *
     * @var array<string, string>
     */
    public array $typeReplacements = [];

    /**
     * Path to the Composer class map
     * Used to discover classes when using auto-discovery
     */
    public string $composerClassMapPath = 'vendor/composer/autoload_classmap.php';

    /**
     * Directories to auto-discover classes from
     *
     * @var string[]
     */
    public array $discoverDirectories = [];

    /**
     * File path to write the generated types to
     */
    public string $outputPath = 'generated.d.ts';

    /**
     * Style of enums to generate
     */
    public EnumStyle $enumStyle = EnumStyle::ENUMS;

    /**
     * Style of records to generate
     */
    public RecordStyle $recordStyle = RecordStyle::INTERFACES;

    /**
     * Set the directory to auto-discover classes from
     *
     * @param  string[]  $directories
     */
    public function withDiscovery(array $directories): self
    {
        $clone = clone $this;
        $clone->discoverDirectories = $directories;

        return $clone;
    }

    /**
     * Set the Composer class map path
     */
    public function withComposerClassMapPath(string $path): self
    {
        $clone = clone $this;
        $clone->composerClassMapPath = $path;

        return $clone;
    }

    /**
     * Set the output file path
     */
    public function withOutputPath(string $path): self
    {
        $clone = clone $this;
        $clone->outputPath = $path;

        return $clone;
    }

    /**
     * Enum style
     */
    public function withEnumsStyle(EnumStyle $style): self
    {
        $clone = clone $this;
        $clone->enumStyle = $style;

        return $clone;
    }

    /**
     * Record style
     */
    public function withRecordsStyle(RecordStyle $style): self
    {
        $clone = clone $this;
        $clone->recordStyle = $style;

        return $clone;
    }

    /**
     * Set the indentation style
     */
    public function withIndent(string $indent): self
    {
        $clone = clone $this;
        $clone->indent = $indent;

        return $clone;
    }

    /**
     * Add a type replacement
     */
    public function withTypeReplacement(string $phpType, string $tsType): self
    {
        $clone = clone $this;
        $clone->typeReplacements[$phpType] = $tsType;

        return $clone;
    }

    /**
     * Generate TypeScript types from the given class names
     * and write them to the file specified in the generator
     *
     * @param  class-string[]  $classNames
     */
    public function generate(array $classNames = []): void
    {
        if (count($this->discoverDirectories) > 0) {
            $classNames = array_unique(array_merge($classNames, ClassDiscovery::discover(
                composerClassMapPath: $this->composerClassMapPath,
                directories: $this->discoverDirectories,
            )));
        }

        if (count($classNames) === 0) {
            throw new InvalidArgumentException('No classes to generate');
        }

        $genCtx = new GenerationContext(
            queue: new Queue($classNames),
            typeReplacements: $this->typeReplacements,
            parentProperty: null,
        );

        /** @var  array<class-string, RecordType|EnumType> */
        $types = [];

        while ($className = $genCtx->queue->shift()) {
            $type = enum_exists($className)
                ? EnumType::from($genCtx, $className)
                : RecordType::from($genCtx, $className);

            $types[$className] = $type;
        }

        $root = RootType::fromTypes($types);

        $renderCtx = new RenderContext(
            indent: $this->indent,
            depth: 0,
            enumStyle: $this->enumStyle,
            recordStyle: $this->recordStyle,
        );

        $ts = $root->render($renderCtx);

        if (
            file_exists($this->outputPath) && !is_writable($this->outputPath)
            || !file_put_contents($this->outputPath, $ts)
        ) {
            throw new RuntimeException('Failed to write generated types to file ' . $this->outputPath);
        }
    }
}
