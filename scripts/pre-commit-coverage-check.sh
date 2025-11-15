#!/bin/bash

# Pre-commit hook to check test coverage
# Blocks commit if coverage decreased
# Updates coverage_percent file if coverage increased

set -e

echo "Running coverage check..."

# Check if coverage_percent file exists
if [ ! -f "coverage_percent" ]; then
    echo "⚠ Warning: coverage_percent file not found. Creating it with current coverage..."
    make test-coverage-save
    git add coverage_percent
    echo "✅ Created coverage_percent file with current coverage"
    exit 0
fi

# Read current threshold
THRESHOLD=$(cat coverage_percent | tr -d '[:space:]')
if [ -z "$THRESHOLD" ]; then
    echo "❌ Error: coverage_percent file is empty"
    exit 1
fi

# Check if docker-compose is available
if ! command -v docker-compose >/dev/null 2>&1; then
    echo "⚠ Warning: docker-compose not found, skipping coverage check"
    exit 0
fi

# Run coverage tests and get current coverage
echo "Running tests with coverage..."
if ! make test-coverage > /dev/null 2>&1; then
    echo "❌ Error: Failed to run coverage tests"
    exit 1
fi

# Extract current coverage percentage
CURRENT_COVERAGE=$(docker-compose exec -T php php scripts/extract-coverage.php coverage/clover.xml 2>/dev/null | tr -d '[:space:]')

if [ -z "$CURRENT_COVERAGE" ]; then
    echo "❌ Error: Failed to extract coverage percentage"
    exit 1
fi

echo "Current coverage: ${CURRENT_COVERAGE}%"
echo "Required threshold: ${THRESHOLD}%"

# Use PHP to compare floating point numbers (more reliable than bc)
COMPARE_RESULT=$(docker-compose exec -T php php -r "
\$current = (float) '${CURRENT_COVERAGE}';
\$threshold = (float) '${THRESHOLD}';
if (\$current < \$threshold) {
    echo 'DECREASED';
} elseif (\$current > \$threshold) {
    echo 'INCREASED';
} else {
    echo 'SAME';
}
")

if [ "$COMPARE_RESULT" = "DECREASED" ]; then
    DIFF=$(docker-compose exec -T php php -r "echo round((float) '${THRESHOLD}' - (float) '${CURRENT_COVERAGE}', 2);")
    echo ""
    echo "❌ Coverage check failed!"
    echo "Current coverage (${CURRENT_COVERAGE}%) is below the required threshold (${THRESHOLD}%)"
    echo "Coverage decreased by ${DIFF}%"
    echo ""
    echo "Commit blocked. Please improve test coverage before committing."
    exit 1
fi

if [ "$COMPARE_RESULT" = "INCREASED" ]; then
    DIFF=$(docker-compose exec -T php php -r "echo round((float) '${CURRENT_COVERAGE}' - (float) '${THRESHOLD}', 2);")
    echo ""
    echo "✅ Coverage increased by ${DIFF}%!"
    echo "Updating coverage_percent file from ${THRESHOLD}% to ${CURRENT_COVERAGE}%"
    
    # Update coverage_percent file
    echo "$CURRENT_COVERAGE" > coverage_percent
    
    # Add to git staging area
    git add coverage_percent
    
    echo "✅ Updated coverage_percent file and added to commit"
fi

echo "✅ Coverage check passed!"
exit 0

