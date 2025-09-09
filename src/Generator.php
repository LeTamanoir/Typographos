<?php

declare(strict_types=1);

namespace Typographos;

use InvalidArgumentException;
use RuntimeException;
use Typographos\Dto\GenCtx;
use Typographos\Dto\RenderCtx;
use Typographos\Dto\RootType;
use Typographos\Enums\EnumStyle;
use Typographos\Enums\RecordStyle;

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
     * Directories to auto-discover classes from
     *
     * @var string[]
     */
    public array $discoverDirectories = [];

    /**
     * File path to write the generated types to
     */
    public string $filePath = 'generated.d.ts';

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
     */
    public function withDiscoverFrom(string ...$directories): self
    {
        $this->discoverDirectories = array_merge($this->discoverDirectories, $directories);

        return $this;
    }

    /**
     * Set the output file path
     */
    public function outputTo(string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * Enum style
     */
    public function withEnumsStyle(EnumStyle $style): self
    {
        $this->enumStyle = $style;

        return $this;
    }

    /**
     * Record style
     */
    public function withRecordsStyle(RecordStyle $style): self
    {
        $this->recordStyle = $style;

        return $this;
    }

    /**
     * Set the indentation style
     */
    public function withIndent(string $indent): self
    {
        $this->indent = $indent;

        return $this;
    }

    /**
     * Add a type replacement
     */
    public function withTypeReplacement(string $phpType, string $tsType): self
    {
        $this->typeReplacements[$phpType] = $tsType;

        return $this;
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
            foreach ($this->discoverDirectories as $directory) {
                $classNames = array_unique(array_merge($classNames, ClassDiscovery::discover($directory)));
            }
        }

        if (count($classNames) === 0) {
            throw new InvalidArgumentException('No classes to generate');
        }

        $genCtx = new GenCtx(
            queue: new Queue($classNames),
            typeReplacements: $this->typeReplacements,
            parentProperty: null,
        );

        $root = RootType::from($genCtx);

        $renderCtx = new RenderCtx(
            indent: $this->indent,
            depth: 0,
            enumStyle: $this->enumStyle,
            recordStyle: $this->recordStyle,
        );

        $ts = $root->render($renderCtx);

        if (file_exists($this->filePath) && !is_writable($this->filePath) || !file_put_contents($this->filePath, $ts)) {
            throw new RuntimeException('Failed to write generated types to file ' . $this->filePath);
        }
    }
}
