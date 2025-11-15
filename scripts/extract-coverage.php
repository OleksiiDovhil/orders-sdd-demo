#!/usr/bin/env php
<?php

/**
 * Extract coverage percentage from PHPUnit Clover XML report
 * Usage: php scripts/extract-coverage.php [path/to/coverage/clover.xml]
 */

$cloverFile = $argv[1] ?? 'coverage/clover.xml';

if (!file_exists($cloverFile)) {
    fwrite(STDERR, "Error: Coverage file not found: {$cloverFile}\n");
    fwrite(STDERR, "Please run 'make test-coverage' first to generate the coverage report.\n");
    exit(1);
}

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

$percentage = ($coveredStatements / $statements) * 100;
$percentage = round($percentage, 2);

echo $percentage . "\n";

