<?php

declare(strict_types=1);

use Typographos\Types\RootType;
use Typographos\Types\RecordType;
use Typographos\Context\RenderContext;
use Typographos\Enums\EnumStyle;
use Typographos\Enums\RecordStyle;

it('renders direct types without namespaces', function (): void {
    $renderCtx = new RenderContext('', 0, EnumStyle::ENUMS, RecordStyle::INTERFACES);

    // Create a RootType manually to add direct types without namespaces
    $rootType = new RootType();

    // Create a simple RecordType for testing
    $recordType = new RecordType('SimpleType', []);

    // Add directly to types array (simulating a type without namespace)
    $rootType->types['SimpleType'] = $recordType;

    $result = $rootType->render($renderCtx);

    // Should render the direct type without namespace wrapper
    expect($result)->toContain('interface SimpleType');
});

it('renders both namespaced and direct types', function (): void {
    $renderCtx = new RenderContext('', 0, EnumStyle::ENUMS, RecordStyle::INTERFACES);

    $rootType = new RootType();

    // Add a direct type
    $directType = new RecordType('DirectType', []);
    $rootType->types['DirectType'] = $directType;

    // Add a namespaced type
    $namespacedType = new RecordType('NamespacedType', []);
    $rootType->addType('App\\Models\\NamespacedType', $namespacedType);

    $result = $rootType->render($renderCtx);

    // Should render both the namespace and the direct type
    expect($result)->toContain('namespace App');
    expect($result)->toContain('interface DirectType');
});