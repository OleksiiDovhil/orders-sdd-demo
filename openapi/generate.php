#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Script to generate OpenAPI documentation from PHP attributes
 * This script scans the Presentation/Controller directory and generates openapi.json
 */

require __DIR__ . '/../vendor/autoload.php';

use OpenApi\Generator;
use Symfony\Component\Yaml\Yaml;

// Generate to both openapi folder and public folder for web access
$outputFile = __DIR__ . '/openapi.json';
$publicOutputFile = __DIR__ . '/../public/openapi.json';
$baseConfigFile = __DIR__ . '/openapi.yaml';
$scanPaths = [
    __DIR__ . '/../src/Presentation/Controller',
    __DIR__ . '/../src/Presentation/Request',
];

echo "Generating OpenAPI documentation...\n";
echo "Scanning paths: " . implode(', ', $scanPaths) . "\n";

try {
    // Load base configuration from YAML file
    $baseConfig = [];
    if (file_exists($baseConfigFile)) {
        echo "Loading base configuration from: {$baseConfigFile}\n";
        $baseConfig = Yaml::parseFile($baseConfigFile);
    }

    // Scan PHP attributes for paths and schemas
    $openapi = Generator::scan($scanPaths, [
        'exclude' => ['vendor', 'tests', 'node_modules'],
    ]);

    $json = $openapi->toJson();
    $data = json_decode($json, true);
    
    // Merge base configuration (info, servers) with scanned paths/schemas
    // Base config takes precedence for info and servers
    if (isset($baseConfig['info'])) {
        $data['info'] = $baseConfig['info'];
    }
    
    if (isset($baseConfig['servers'])) {
        $data['servers'] = $baseConfig['servers'];
    }
    
    // Ensure openapi version is set
    if (isset($baseConfig['openapi'])) {
        $data['openapi'] = $baseConfig['openapi'];
    }

    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
    // Write to both locations
    file_put_contents($outputFile, $json);
    file_put_contents($publicOutputFile, $json);
    
    $pathCount = count($data['paths'] ?? []);
    echo "✓ OpenAPI documentation generated successfully!\n";
    echo "  Output: {$outputFile}\n";
    echo "  Public: {$publicOutputFile}\n";
    echo "  Paths found: {$pathCount}\n";
    echo "  Size: " . number_format(strlen($json)) . " bytes\n";
    
    if ($pathCount === 0) {
        echo "  ⚠ Warning: No paths found in documentation\n";
        exit(1);
    }
    
    exit(0);
} catch (\Exception $e) {
    echo "✗ Error generating OpenAPI documentation: " . $e->getMessage() . "\n";
    exit(1);
}

