#!/usr/bin/env php
<?php

/**
 * Automated coverage fix workflow
 * 1. Run tests with coverage
 * 2. Check coverage threshold
 * 3. If failed, analyze and prioritize uncovered code (prioritizing recently modified files)
 * 4. Guide agent to add tests until coverage passes
 * 
 * Usage: php scripts/auto-fix-coverage.php [--git-diff] [--min-priority-coverage=XX]
 */

$useGitDiff = in_array('--git-diff', $argv);
$minPriorityCoverage = 70; // Minimum coverage to prioritize a file

// Parse arguments
foreach ($argv as $arg) {
    if (strpos($arg, '--min-priority-coverage=') === 0) {
        $minPriorityCoverage = (float) substr($arg, strlen('--min-priority-coverage='));
    }
}

echo "🔍 Automated Coverage Fix Workflow\n";
echo "===================================\n\n";

// Step 1: Run tests with coverage
// Note: This script runs inside Docker container, so we can call PHPUnit directly
echo "Step 1: Running tests with coverage...\n";
putenv('XDEBUG_MODE=coverage');
$output = [];
$returnCode = 0;
@mkdir('coverage', 0755, true);
exec('vendor/bin/phpunit --coverage-html coverage/ --coverage-clover coverage/clover.xml --coverage-text=coverage/coverage.txt tests/ 2>&1', $output, $returnCode);
if ($returnCode !== 0) {
    echo "❌ Failed to generate coverage report\n";
    echo implode("\n", array_slice($output, -10)) . "\n";
    exit(1);
}
echo "✅ Coverage reports generated\n\n";

// Step 2: Check coverage threshold
echo "Step 2: Checking coverage threshold...\n";

// Use the same extraction method as test-coverage-percent
$currentCoverage = null;
$extractOutput = [];
exec('php scripts/extract-coverage.php coverage/clover.xml 2>&1', $extractOutput, $extractReturnCode);
if ($extractReturnCode === 0 && !empty($extractOutput)) {
    $currentCoverage = (float) trim($extractOutput[0]);
}

if ($currentCoverage === null) {
    echo "❌ Failed to extract coverage percentage\n";
    exit(1);
}

// Set threshold to 100%
$threshold = 100.0;

// Check if coverage meets threshold
if ($currentCoverage >= $threshold) {
    echo "✅ Coverage check passed! No action needed.\n";
    echo "   Current: {$currentCoverage}%\n";
    echo "   Required: {$threshold}%\n";
    exit(0);
}

$difference = $threshold - $currentCoverage;
echo "❌ Coverage check failed!\n";
echo "   Current: {$currentCoverage}%\n";
echo "   Required: {$threshold}%\n";
echo "   Need to improve by: {$difference}%\n\n";

// Step 3: Analyze and prioritize uncovered code
echo "Step 3: Analyzing uncovered code and prioritizing...\n\n";

// Get recently modified files if git-diff is enabled
$recentFiles = [];
if ($useGitDiff) {
    echo "📝 Detecting recently modified files from git...\n";
    $gitOutput = [];
    exec('git diff --name-only HEAD 2>/dev/null', $gitOutput);
    exec('git diff --cached --name-only 2>/dev/null', $stagedFiles);
    $recentFiles = array_merge($gitOutput, $stagedFiles);
    $recentFiles = array_filter($recentFiles, function($file) {
        return strpos($file, 'src/') === 0 && substr($file, -4) === '.php';
    });
    if (!empty($recentFiles)) {
        echo "   Found " . count($recentFiles) . " recently modified PHP files:\n";
        foreach (array_slice($recentFiles, 0, 5) as $file) {
            echo "     - {$file}\n";
        }
        if (count($recentFiles) > 5) {
            echo "     ... and " . (count($recentFiles) - 5) . " more\n";
        }
    } else {
        echo "   No recently modified PHP files found in git diff\n";
    }
}
echo "\n";

// Parse text coverage report
$uncoveredClasses = [];
$textCoverageFile = 'coverage/coverage.txt';
if (file_exists($textCoverageFile)) {
    $content = file_get_contents($textCoverageFile);
    $lines = explode("\n", $content);
    $currentClass = null;
    
    foreach ($lines as $line) {
        $line = rtrim($line);
        
        // Match class name
        if (preg_match('/^([A-Z][a-zA-Z0-9_\\\\]+)$/', $line, $matches)) {
            $currentClass = $matches[1];
            continue;
        }
        
        // Match coverage
        if ($currentClass && preg_match('/^\s+Methods:\s+(\d+\.?\d*)%\s+\(\s*(\d+)\s*\/\s*(\d+)\)\s+Lines:\s+(\d+\.?\d*)%\s+\(\s*(\d+)\s*\/\s*(\d+)\)/', $line, $matches)) {
            $linesCoverage = (float) $matches[4];
            $linesCovered = (int) $matches[5];
            $linesTotal = (int) $matches[6];
            $methodsCoverage = (float) $matches[1];
            $methodsCovered = (int) $matches[2];
            $methodsTotal = (int) $matches[3];
            
            if ($linesCoverage < 100 && $linesTotal > 0) {
                // Convert class name to file path
                $filePath = str_replace('App\\', 'src/', $currentClass);
                $filePath = str_replace('\\', '/', $filePath) . '.php';
                
                // Check if this file was recently modified
                $isRecent = false;
                foreach ($recentFiles as $recentFile) {
                    if (strpos($filePath, $recentFile) !== false || strpos($recentFile, $filePath) !== false) {
                        $isRecent = true;
                        break;
                    }
                }
                
                $uncoveredClasses[] = [
                    'class' => $currentClass,
                    'file' => $filePath,
                    'lines_coverage' => $linesCoverage,
                    'lines_covered' => $linesCovered,
                    'lines_total' => $linesTotal,
                    'lines_uncovered' => $linesTotal - $linesCovered,
                    'methods_coverage' => $methodsCoverage,
                    'methods_covered' => $methodsCovered,
                    'methods_total' => $methodsTotal,
                    'methods_uncovered' => $methodsTotal - $methodsCovered,
                    'is_recent' => $isRecent,
                    'priority_score' => $isRecent ? 1000 - $linesCoverage : 100 - $linesCoverage, // Higher score = higher priority
                ];
            }
            $currentClass = null;
        }
    }
}

// Sort by priority (recent files first, then by coverage lowest first)
usort($uncoveredClasses, function ($a, $b) {
    // First prioritize recent files
    if ($a['is_recent'] && !$b['is_recent']) return -1;
    if (!$a['is_recent'] && $b['is_recent']) return 1;
    // Then by priority score (higher = more important)
    return $b['priority_score'] <=> $a['priority_score'];
});

if (empty($uncoveredClasses)) {
    echo "⚠ No uncovered classes found in coverage report.\n";
    echo "   This may indicate configuration issues.\n";
    exit(1);
}

// Display prioritized list
echo "📋 Prioritized list of classes needing test coverage:\n";
echo "   (Recently modified files are marked with ⭐)\n\n";

$recentCount = 0;
$otherCount = 0;

foreach ($uncoveredClasses as $index => $class) {
    $priority = $index + 1;
    $star = $class['is_recent'] ? '⭐ ' : '   ';
    
    if ($class['is_recent']) {
        $recentCount++;
    } else {
        $otherCount++;
    }
    
    echo "{$star}[Priority {$priority}] {$class['class']}\n";
    echo "      File: {$class['file']}\n";
    echo "      Lines: {$class['lines_coverage']}% ({$class['lines_covered']}/{$class['lines_total']}) - {$class['lines_uncovered']} uncovered\n";
    echo "      Methods: {$class['methods_coverage']}% ({$class['methods_covered']}/{$class['methods_total']}) - {$class['methods_uncovered']} uncovered\n";
    echo "\n";
    
    // Limit output to top 10
    if ($priority >= 10) {
        $remaining = count($uncoveredClasses) - 10;
        if ($remaining > 0) {
            echo "   ... and {$remaining} more classes\n\n";
        }
        break;
    }
}

echo "📊 Summary:\n";
echo "   Total classes needing coverage: " . count($uncoveredClasses) . "\n";
if ($recentCount > 0) {
    echo "   ⭐ Recently modified (HIGH PRIORITY): {$recentCount}\n";
}
if ($otherCount > 0) {
    echo "   Other files: {$otherCount}\n";
}
echo "\n";

// Step 4: Provide guidance
echo "Step 4: Action Plan\n";
echo "===================\n\n";
echo "💡 Next steps:\n";
echo "   1. Start with the highest priority class (marked with ⭐ if recently modified)\n";
echo "   2. Create or update tests for that class\n";
echo "   3. Run: make test-coverage-check\n";
echo "   4. Repeat until coverage check passes\n\n";

echo "🎯 Focus on:\n";
if ($recentCount > 0) {
    echo "   - Recently modified files (marked ⭐) - these are HIGHEST PRIORITY\n";
}
echo "   - Files with lowest coverage percentages\n";
echo "   - Files with most uncovered methods/lines\n\n";

echo "📝 Example workflow:\n";
echo "   1. Review: {$uncoveredClasses[0]['file']}\n";
echo "   2. Create tests in: tests/" . str_replace('src/', '', dirname($uncoveredClasses[0]['file'])) . "/" . basename($uncoveredClasses[0]['file'], '.php') . "Test.php\n";
echo "   3. Run: make test-coverage-check\n";
echo "   4. If still failing, move to next priority class\n\n";

exit(1); // Exit with error to indicate coverage needs improvement

