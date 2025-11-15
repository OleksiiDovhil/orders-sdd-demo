#!/usr/bin/env php
<?php

/**
 * Check if test coverage meets or exceeds 100% threshold
 * Usage: php scripts/check-coverage-threshold.php [path/to/coverage/clover.xml]
 */

$cloverFile = $argv[1] ?? 'coverage/clover.xml';

// Set threshold to 100%
$threshold = 100.0;

// Check if coverage file exists
if (!file_exists($cloverFile)) {
    fwrite(STDERR, "Error: Coverage file not found: {$cloverFile}\n");
    fwrite(STDERR, "Please run 'make test-coverage' first to generate the coverage report.\n");
    exit(1);
}

// Parse coverage XML
$xml = simplexml_load_file($cloverFile);
if ($xml === false) {
    fwrite(STDERR, "Error: Failed to parse XML file: {$cloverFile}\n");
    exit(1);
}

$metrics = $xml->project->metrics;
$statements = (int) $metrics['statements'];
$coveredStatements = (int) $metrics['coveredstatements'];

if ($statements === 0) {
    fwrite(STDERR, "Error: No statements found in coverage report\n");
    exit(1);
}

$currentCoverage = ($coveredStatements / $statements) * 100;
$currentCoverage = round($currentCoverage, 2);

// Compare coverage
echo "Current coverage: {$currentCoverage}%\n";
echo "Required threshold: {$threshold}%\n";

if ($currentCoverage < $threshold) {
    $difference = $threshold - $currentCoverage;
    fwrite(STDERR, "\n❌ Coverage check failed!\n");
    fwrite(STDERR, "Current coverage ({$currentCoverage}%) is below the required threshold ({$threshold}%)\n");
    fwrite(STDERR, "Coverage decreased by {$difference}%\n");
    exit(1);
}

echo "✅ Coverage check passed!\n";
echo "Current coverage ({$currentCoverage}%) meets or exceeds the threshold ({$threshold}%)\n";
exit(0);

