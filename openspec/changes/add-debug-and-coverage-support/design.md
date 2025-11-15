# Design: Debug and Coverage Support

## Context
The project currently uses PHPUnit for testing but lacks:
1. Xdebug support for debugging PHP scripts in Docker
2. Codeception integration for advanced testing and coverage reporting
3. Persistent coverage report storage
4. Automated coverage percentage tracking in README

## Goals / Non-Goals

### Goals
- Enable Xdebug debugging in Docker PHP container
- Integrate Codeception alongside PHPUnit
- Generate and persist coverage reports
- Automatically extract and display coverage percentage in README

### Non-Goals
- Replace PHPUnit with Codeception (both will coexist)
- Set up remote debugging configuration (focus on local debugging)
- Implement coverage thresholds or gates (future enhancement)

## Decisions

### Decision: Install Xdebug in Docker PHP Container
**What**: Add Xdebug PHP extension to the Docker PHP-FPM container with configuration for local debugging.

**Why**: 
- Enables step-through debugging of PHP scripts and tests
- Essential for debugging complex test scenarios
- Standard practice for PHP development environments

**Alternatives considered**:
- Use remote debugging only - Rejected: Local debugging is simpler and more reliable for development
- Use alternative debugger (Zend Debugger) - Rejected: Xdebug is the de-facto standard

### Decision: Add Codeception for Coverage Reporting
**What**: Install Codeception as a dev dependency and configure it for coverage reporting alongside PHPUnit.

**Why**:
- Codeception provides advanced coverage reporting capabilities
- Can work alongside PHPUnit without conflicts
- Better integration with various coverage formats

**Alternatives considered**:
- Use only PHPUnit coverage - Rejected: Codeception provides better coverage reporting features
- Replace PHPUnit with Codeception - Rejected: PHPUnit is already established and working

### Decision: Save Coverage Reports to `coverage/` Directory
**What**: Configure coverage reports to be saved in a `coverage/` directory that is gitignored but persists locally.

**Why**:
- Allows developers to view HTML coverage reports
- Prevents coverage files from being committed
- Standard location for coverage reports

**Alternatives considered**:
- Save to `var/coverage/` - Rejected: `coverage/` is more standard and discoverable
- Commit coverage reports - Rejected: Coverage reports are generated artifacts

### Decision: Extract Coverage Percentage from PHPUnit XML Report
**What**: Use PHPUnit's `--coverage-text` or parse coverage XML to extract percentage, then update README.

**Why**:
- PHPUnit already generates coverage data
- Can be automated in Makefile
- Provides accurate coverage metrics

**Alternatives considered**:
- Manual coverage tracking - Rejected: Too error-prone and not automated
- Use Codeception coverage only - Rejected: PHPUnit coverage is already configured

## Risks / Trade-offs

### Risk: Xdebug Performance Impact
**Mitigation**: Xdebug can be disabled via environment variable when not needed. Performance impact is acceptable for development.

### Risk: Codeception and PHPUnit Configuration Conflicts
**Mitigation**: Configure Codeception to use PHPUnit as its test runner, ensuring compatibility.

### Risk: Coverage Report Generation Time
**Mitigation**: Coverage generation is optional and only runs when explicitly requested via `make test-coverage`.

## Migration Plan

1. Add Xdebug to Dockerfile
2. Add Codeception to composer.json
3. Configure Codeception
4. Update Makefile coverage target
5. Add coverage extraction script/target
6. Update README with coverage percentage
7. Test debugging functionality
8. Verify coverage reports are generated correctly

## Open Questions
- Should Xdebug be enabled by default or opt-in via environment variable?
  - **Decision**: Enable by default in development, can be disabled via env var
- What coverage format should be used (HTML, XML, Clover)?
  - **Decision**: Generate HTML for viewing, XML for parsing percentage
- Should coverage percentage be updated automatically on every test run or only on coverage runs?
  - **Decision**: Only update when `make test-coverage` is run

