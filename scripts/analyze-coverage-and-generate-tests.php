#!/usr/bin/env php
<?php

/**
 * Analyze coverage report and identify uncovered code to help restore coverage
 * Usage: php scripts/analyze-coverage-and-generate-tests.php [path/to/coverage/clover.xml]
 * 
 * This script analyzes the coverage report to find uncovered classes, methods, and lines.
 * It's particularly useful when coverage has decreased and you need to identify what needs tests.
 */

$cloverFile = $argv[1] ?? 'coverage/clover.xml';

// Check if coverage file exists
if (!file_exists($cloverFile)) {
    fwrite(STDERR, "Error: Coverage file not found: {$cloverFile}\n");
    fwrite(STDERR, "Please run 'make test-coverage' first to generate the coverage report.\n");
    exit(1);
}

// Set threshold to 100%
$threshold = 100.0;

// Parse coverage XML
$xml = simplexml_load_file($cloverFile);
if ($xml === false) {
    fwrite(STDERR, "Error: Failed to parse XML file: {$cloverFile}\n");
    exit(1);
}

// Get current coverage
$metrics = $xml->project->metrics;
$statements = (int) $metrics['statements'];
$coveredStatements = (int) $metrics['coveredstatements'];

if ($statements === 0) {
    fwrite(STDERR, "Error: No statements found in coverage report\n");
    exit(1);
}

$currentCoverage = ($coveredStatements / $statements) * 100;
$currentCoverage = round($currentCoverage, 2);

echo "Current coverage: {$currentCoverage}%\n";
echo "Required threshold: {$threshold}%\n\n";

// Check if coverage decreased
if ($currentCoverage < $threshold) {
    $difference = $threshold - $currentCoverage;
    echo "❌ Coverage decreased by {$difference}%\n";
    echo "Analyzing uncovered code...\n\n";
    
    // Find uncovered files and methods
    $uncoveredFiles = [];
    
    foreach ($xml->project->file as $file) {
        $filePath = (string) $file['name'];
        $fileMetrics = $file->metrics;
        $fileStatements = (int) $fileMetrics['statements'];
        $fileCovered = (int) $fileMetrics['coveredstatements'];
        
        if ($fileStatements > 0 && $fileCovered < $fileStatements) {
            $fileCoverage = ($fileCovered / $fileStatements) * 100;
            $uncoveredMethods = [];
            $uncoveredLines = [];
            
            // Find uncovered methods
            foreach ($file->class as $class) {
                foreach ($class->method as $method) {
                    $methodMetrics = $method->metrics;
                    $methodStatements = (int) $methodMetrics['statements'];
                    $methodCovered = (int) $methodMetrics['coveredstatements'];
                    
                    if ($methodStatements > 0 && $methodCovered < $methodStatements) {
                        $methodName = (string) $method['name'];
                        $className = (string) $class['name'];
                        $uncoveredMethods[] = [
                            'class' => $className,
                            'method' => $methodName,
                            'coverage' => round(($methodCovered / $methodStatements) * 100, 2),
                        ];
                    }
                }
            }
            
            // Find uncovered lines
            foreach ($file->line as $line) {
                $lineType = (string) $line['type'];
                $lineNum = (int) $line['num'];
                $lineCount = (int) $line['count'];
                
                if ($lineType === 'stmt' && $lineCount === 0) {
                    $uncoveredLines[] = $lineNum;
                }
            }
            
            if (!empty($uncoveredMethods) || !empty($uncoveredLines)) {
                $uncoveredFiles[] = [
                    'path' => $filePath,
                    'coverage' => round($fileCoverage, 2),
                    'methods' => $uncoveredMethods,
                    'lines' => array_slice($uncoveredLines, 0, 20), // Limit to first 20 lines
                ];
            }
        }
    }
    
    // Sort by coverage (lowest first)
    usort($uncoveredFiles, function ($a, $b) {
        return $a['coverage'] <=> $b['coverage'];
    });
    
    // Display findings
    if (empty($uncoveredFiles)) {
        echo "⚠ Note: All files in the XML coverage report are fully covered.\n";
        echo "However, overall coverage ({$currentCoverage}%) is still below threshold ({$threshold}%).\n";
        echo "Trying to parse text coverage report for more detailed method-level analysis...\n\n";
        
        // Try to parse text coverage report as fallback
        $textCoverageFile = dirname($cloverFile) . '/coverage.txt';
        if (file_exists($textCoverageFile)) {
            passthru("php scripts/parse-coverage-text.php " . escapeshellarg($textCoverageFile));
            exit(1);
        } else {
            echo "💡 Recommendation:\n";
            echo "  1. Check phpunit.xml.dist to ensure all source files are included\n";
            echo "  2. Review the HTML coverage report (http://localhost:8080/coverage/) for detailed line-by-line analysis\n";
            echo "  3. Review the text coverage report (coverage/coverage.txt) for method-level details\n";
            echo "  4. Add tests for any untested code paths\n";
            echo "  5. Verify that all source files are being tracked in the coverage report\n";
            exit(1);
        }
    }
    
    echo "Found " . count($uncoveredFiles) . " files with uncovered code:\n\n";
    
    foreach (array_slice($uncoveredFiles, 0, 10) as $file) {
        $relativePath = str_replace(getcwd() . '/', '', $file['path']);
        $relativePath = str_replace('/var/www/html/', '', $relativePath);
        echo "📄 {$relativePath} (Coverage: {$file['coverage']}%)\n";
        
        if (!empty($file['methods'])) {
            echo "   Uncovered methods:\n";
            foreach (array_slice($file['methods'], 0, 5) as $method) {
                echo "     - {$method['class']}::{$method['method']()} ({$method['coverage']}%)\n";
            }
            if (count($file['methods']) > 5) {
                echo "     ... and " . (count($file['methods']) - 5) . " more\n";
            }
        }
        
        if (!empty($file['lines'])) {
            $lineCount = count($file['lines']);
            $linesStr = implode(', ', array_slice($file['lines'], 0, 10));
            if ($lineCount > 10) {
                $linesStr .= " ... (+" . ($lineCount - 10) . " more)";
            }
            echo "   Uncovered lines: {$linesStr}\n";
        }
        
        echo "\n";
    }
    
    if (count($uncoveredFiles) > 10) {
        echo "... and " . (count($uncoveredFiles) - 10) . " more files\n\n";
    }
    
    echo "💡 Recommendation: Create or update tests for the uncovered code above.\n";
    echo "   Focus on files with the lowest coverage first.\n";
    echo "   Run 'make test-coverage' after adding tests to verify improvement.\n";
    echo "\n";
    echo "📄 For detailed method-level analysis, see: coverage/coverage.txt\n";
    echo "📄 For interactive HTML report, visit: http://localhost:8080/coverage/\n";
    
    exit(1);
}

echo "✅ Coverage check passed! Current coverage ({$currentCoverage}%) meets or exceeds threshold ({$threshold}%)\n";
exit(0);

