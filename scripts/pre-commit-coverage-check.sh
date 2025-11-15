#!/bin/bash

# Pre-commit hook to check test coverage
# Blocks commit if coverage is below 100%

set -e

echo "Running coverage check..."

# Set threshold to 100%
THRESHOLD=100.0

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
    echo "✅ Coverage is ${CURRENT_COVERAGE}% (above 100% threshold)!"
fi

echo "✅ Coverage check passed!"
exit 0

