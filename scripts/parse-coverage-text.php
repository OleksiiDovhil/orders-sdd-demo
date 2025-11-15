#!/usr/bin/env php
<?php

/**
 * Parse PHPUnit text coverage report and extract uncovered files, methods, and lines
 * Usage: php scripts/parse-coverage-text.php [path/to/coverage/coverage.txt]
 */

$coverageFile = $argv[1] ?? 'coverage/coverage.txt';

if (!file_exists($coverageFile)) {
    fwrite(STDERR, "Error: Coverage text file not found: {$coverageFile}\n");
    fwrite(STDERR, "Please run 'make test-coverage' first to generate the coverage report.\n");
    exit(1);
}

$content = file_get_contents($coverageFile);
if ($content === false) {
    fwrite(STDERR, "Error: Failed to read coverage file: {$coverageFile}\n");
    exit(1);
}

// Parse the text coverage report
// Format is:
// ClassName
//   Methods: XX.XX% (X/Y)   Lines: XX.XX% (X/Y)

$uncoveredFiles = [];
$currentClass = null;
$lines = explode("\n", $content);

foreach ($lines as $lineNum => $line) {
    $line = rtrim($line);
    
    // Skip empty lines, headers, and summary
    if (empty($line) || 
        strpos($line, 'Code Coverage Report') !== false || 
        strpos($line, 'Summary:') !== false ||
        strpos($line, '---') !== false ||
        preg_match('/^\s*Classes:\s*\d+\.\d+%/', $line) ||
        preg_match('/^\s*Methods:\s*\d+\.\d+%/', $line) && strpos($content, 'Summary:') !== false && $lineNum < 10) {
        continue;
    }
    
    // Match class name (starts at beginning of line, no indentation)
    // Format: App\Namespace\ClassName
    if (preg_match('/^([A-Z][a-zA-Z0-9_\\\\]+)$/', $line, $matches)) {
        $currentClass = $matches[1];
        continue;
    }
    
    // Match Methods and Lines coverage for the current class
    // Format:   Methods:  XX.XX% ( X/Y)   Lines:  XX.XX% ( X/Y)
    // Note: Percentages and numbers may have leading spaces
    if ($currentClass && preg_match('/^\s+Methods:\s+(\d+\.?\d*)%\s+\(\s*(\d+)\s*\/\s*(\d+)\)\s+Lines:\s+(\d+\.?\d*)%\s+\(\s*(\d+)\s*\/\s*(\d+)\)/', $line, $matches)) {
        $methodsCoverage = (float) $matches[1];
        $methodsCovered = (int) $matches[2];
        $methodsTotal = (int) $matches[3];
        $linesCoverage = (float) $matches[4];
        $linesCovered = (int) $matches[5];
        $linesTotal = (int) $matches[6];
        
        // Use lines coverage as primary metric, but track both
        if ($linesCoverage < 100 && $linesTotal > 0) {
            $uncoveredFiles[$currentClass] = [
                'class' => $currentClass,
                'methods_coverage' => $methodsCoverage,
                'methods_covered' => $methodsCovered,
                'methods_total' => $methodsTotal,
                'methods_uncovered' => $methodsTotal - $methodsCovered,
                'lines_coverage' => $linesCoverage,
                'lines_covered' => $linesCovered,
                'lines_total' => $linesTotal,
                'lines_uncovered' => $linesTotal - $linesCovered,
            ];
        }
        $currentClass = null; // Reset after processing
    }
}

// Sort by lines coverage (lowest first)
usort($uncoveredFiles, function ($a, $b) {
    return $a['lines_coverage'] <=> $b['lines_coverage'];
});

if (empty($uncoveredFiles)) {
    echo "✅ All classes in the coverage report are fully covered.\n";
    exit(0);
}

echo "Found " . count($uncoveredFiles) . " classes with uncovered code:\n\n";

foreach (array_slice($uncoveredFiles, 0, 20) as $class) {
    echo "📄 {$class['class']}\n";
    echo "   Lines Coverage: {$class['lines_coverage']}% ({$class['lines_covered']}/{$class['lines_total']} lines)\n";
    echo "   Uncovered Lines: {$class['lines_uncovered']}\n";
    echo "   Methods Coverage: {$class['methods_coverage']}% ({$class['methods_covered']}/{$class['methods_total']} methods)\n";
    if ($class['methods_uncovered'] > 0) {
        echo "   Uncovered Methods: {$class['methods_uncovered']}\n";
    }
    echo "\n";
}

if (count($uncoveredFiles) > 20) {
    echo "... and " . (count($uncoveredFiles) - 20) . " more files\n\n";
}

echo "💡 Recommendation: Create or update tests for the uncovered code above.\n";
echo "   Focus on files with the lowest coverage first.\n";
echo "   Run 'make test-coverage' after adding tests to verify improvement.\n";

