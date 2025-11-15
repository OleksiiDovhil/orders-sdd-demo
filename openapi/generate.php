#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Script to generate OpenAPI documentation from PHP attributes
 * This script scans the Presentation/Controller directory and generates openapi.json
 */

require __DIR__ . '/../vendor/autoload.php';

use OpenApi\Generator;

// Generate to both openapi folder and public folder for web access
$outputFile = __DIR__ . '/openapi.json';
$publicOutputFile = __DIR__ . '/../public/openapi.json';
$scanPaths = [__DIR__ . '/../src/Presentation/Controller'];

echo "Generating OpenAPI documentation...\n";
echo "Scanning paths: " . implode(', ', $scanPaths) . "\n";

try {
    $openapi = Generator::scan($scanPaths, [
        'exclude' => ['vendor', 'tests', 'node_modules'],
    ]);

    $json = $openapi->toJson();
    
    // Merge with base configuration from nelmio config
    $data = json_decode($json, true);
    
    // Add info from config if not present
    if (!isset($data['info'])) {
        $data['info'] = [
            'title' => 'Order Management API',
            'description' => 'API for creating and managing orders',
            'version' => '1.0.0',
        ];
    }
    
    if (!isset($data['servers'])) {
        $data['servers'] = [
            [
                'url' => 'http://localhost:8080',
                'description' => 'Local development server',
            ],
        ];
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

