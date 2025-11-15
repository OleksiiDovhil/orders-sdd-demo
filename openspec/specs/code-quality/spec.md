# code-quality Specification

## Purpose
TBD - created by archiving change add-phpstan-code-quality. Update Purpose after archive.
## Requirements
### Requirement: PHPStan Static Analysis
The system SHALL provide PHPStan static analysis tool configured at level 9 to detect type errors, potential bugs, and code quality issues before code is committed.

#### Scenario: Run PHPStan analysis
- **WHEN** a developer runs `make phpstan`
- **THEN** PHPStan analyzes all PHP files in `src/` and `tests/` directories
- **AND** PHPStan runs at level 9 (maximum strictness)
- **AND** the analysis executes inside the Docker PHP container
- **AND** the command outputs all detected issues or confirms no issues found

#### Scenario: PHPStan detects type errors
- **WHEN** PHPStan analyzes code with type errors or potential bugs
- **THEN** PHPStan reports specific file locations and error descriptions
- **AND** the exit code is non-zero to indicate failure
- **AND** developers can use the output to fix the issues

#### Scenario: PHPStan passes with no issues
- **WHEN** PHPStan analyzes code with no type errors or issues
- **THEN** PHPStan reports success
- **AND** the exit code is zero
- **AND** the command completes successfully

### Requirement: PHPStan Configuration
The system SHALL provide a PHPStan configuration file that defines analysis rules, paths, and extensions.

#### Scenario: PHPStan configuration exists
- **WHEN** PHPStan is executed
- **THEN** it reads configuration from `phpstan.neon` or `phpstan.dist.neon`
- **AND** the configuration specifies level 9
- **AND** the configuration includes `src/` and `tests/` directories for analysis
- **AND** the configuration includes necessary extensions for Symfony and PHPUnit support

#### Scenario: PHPStan ignores specific issues
- **WHEN** certain PHPStan issues cannot be fixed after multiple attempts
- **THEN** appropriate ignore annotations or configuration entries are added
- **AND** the ignored issues are documented with reasons
- **AND** PHPStan still validates all other code at level 9

### Requirement: Makefile Integration
The system SHALL provide a Makefile target to run PHPStan analysis.

#### Scenario: Execute PHPStan via Makefile
- **WHEN** a developer runs `make phpstan`
- **THEN** PHPStan executes inside the Docker PHP container
- **AND** the command uses `docker-compose exec -T php` to run in non-interactive mode
- **AND** the output is displayed in the terminal
- **AND** the command exits with appropriate code (0 for success, non-zero for errors)

### Requirement: Pre-commit Hook Validation
The system SHALL run code quality and style validation automatically before allowing code commits, in the following order:
1. phpcs (CodeSniffer checker - code style validation)
2. PHPStan analysis (static analysis)
3. Deptrack analysis (dependency tracking)
4. Test execution (all tests)
5. OpenAPI generation (documentation)

#### Scenario: Pre-commit hook runs all validations in order
- **WHEN** a developer attempts to commit code
- **THEN** the pre-commit hook executes validations in the specified order
- **AND** each validation runs inside the Docker PHP container
- **AND** the hook waits for each validation to complete before proceeding to the next
- **AND** if any validation fails, subsequent validations are skipped and the commit is blocked

#### Scenario: Pre-commit hook blocks commit on any validation failure
- **WHEN** any validation (phpcs, PHPStan, deptrack, or tests) fails during pre-commit hook execution
- **THEN** the commit is blocked
- **AND** an error message is displayed indicating which validation failed
- **AND** the developer must fix the issues before committing
- **AND** the exit code prevents the commit from proceeding

#### Scenario: Pre-commit hook allows commit when all validations pass
- **WHEN** all validations pass during pre-commit hook execution
- **THEN** the commit proceeds normally
- **AND** OpenAPI documentation is generated and staged
- **AND** the commit completes successfully

### Requirement: Deptrack Dependency Tracking
The system SHALL provide deptrack dependency tracking tool configured with DDD architectural rules to enforce layer boundaries and prevent improper dependencies between architectural layers.

#### Scenario: Run deptrack analysis
- **WHEN** a developer runs `make deptrack`
- **THEN** deptrack analyzes all PHP files in `src/` directory
- **AND** deptrack validates dependencies against configured DDD rules
- **AND** the analysis executes inside the Docker PHP container
- **AND** the command outputs all detected architectural violations or confirms no violations found

#### Scenario: Deptrack detects architectural violations
- **WHEN** deptrack analyzes code with improper layer dependencies
- **THEN** deptrack reports specific file locations and dependency violations
- **AND** the exit code is non-zero to indicate failure
- **AND** developers can use the output to identify and fix architectural issues

#### Scenario: Deptrack passes with no violations
- **WHEN** deptrack analyzes code with proper layer dependencies
- **THEN** deptrack reports success
- **AND** the exit code is zero
- **AND** the command completes successfully

### Requirement: Deptrack DDD Configuration
The system SHALL provide a deptrack configuration file that defines DDD layer structure and dependency rules.

#### Scenario: Deptrack configuration exists
- **WHEN** deptrack is executed
- **THEN** it reads configuration from `deptrack.yaml` or `deptrack.php` (or similar)
- **AND** the configuration defines four architectural layers:
  - Domain layer (`src/Domain/**`)
  - Application layer (`src/Application/**`)
  - Infrastructure layer (`src/Infrastructure/**`)
  - Presentation layer (`src/Presentation/**`)
- **AND** the configuration enforces dependency rules:
  - Domain layer must not depend on other layers
  - Application layer may depend on Domain layer only
  - Infrastructure layer may depend on Domain and Application layers
  - Presentation layer may depend on Application and Domain layers
- **AND** circular dependencies are prevented within and across layers

#### Scenario: Deptrack validates layer boundaries
- **WHEN** code violates DDD layer dependency rules
- **THEN** deptrack reports the violation with specific file and dependency information
- **AND** the violation includes source layer, target layer, and file locations
- **AND** developers can use this information to refactor the code

### Requirement: Makefile Integration for Deptrack
The system SHALL provide a Makefile target to run deptrack analysis.

#### Scenario: Execute deptrack via Makefile
- **WHEN** a developer runs `make deptrack`
- **THEN** deptrack executes inside the Docker PHP container
- **AND** the command uses `docker-compose exec -T php` to run in non-interactive mode
- **AND** the output is displayed in the terminal
- **AND** the command exits with appropriate code (0 for success, non-zero for violations)

### Requirement: Pre-commit Hook Validation with Deptrack
The system SHALL run deptrack analysis automatically before allowing code commits.

#### Scenario: Pre-commit hook runs deptrack
- **WHEN** a developer attempts to commit code
- **THEN** the pre-commit hook executes deptrack analysis
- **AND** deptrack runs inside the Docker PHP container
- **AND** the hook waits for deptrack to complete before proceeding

#### Scenario: Pre-commit hook blocks commit on deptrack failure
- **WHEN** deptrack detects architectural violations during pre-commit hook execution
- **THEN** the commit is blocked
- **AND** an error message is displayed indicating deptrack violations
- **AND** the developer must fix the architectural issues before committing
- **AND** the exit code prevents the commit from proceeding

#### Scenario: Pre-commit hook allows commit on deptrack success
- **WHEN** deptrack passes with no violations during pre-commit hook execution
- **THEN** the commit proceeds normally
- **AND** other pre-commit hook tasks (like PHPStan and OpenAPI generation) continue to execute
- **AND** the commit completes successfully

### Requirement: phpcbf Code Formatting
The system SHALL provide phpcbf (PHP Code Beautifier and Fixer) tool configured for PSR-12 standards to automatically fix code style issues.

#### Scenario: Run phpcbf to fix code style
- **WHEN** a developer runs `make phpcbf`
- **THEN** phpcbf automatically fixes all fixable code style issues in `src/` and `tests/` directories
- **AND** phpcbf runs inside the Docker PHP container
- **AND** the command modifies files in place to fix style violations
- **AND** the command outputs a summary of files fixed or confirms no fixes needed

#### Scenario: phpcbf detects and fixes style violations
- **WHEN** phpcbf analyzes code with style violations
- **THEN** phpcbf automatically fixes fixable violations
- **AND** phpcbf reports specific file locations and violation descriptions for fixed issues
- **AND** the exit code is zero if all violations were fixed, non-zero if some violations remain

#### Scenario: phpcbf passes with no violations
- **WHEN** phpcbf analyzes code with no style violations
- **THEN** phpcbf reports success
- **AND** the exit code is zero
- **AND** the command completes successfully

### Requirement: PHP CodeSniffer Code Style Validation
The system SHALL provide phpcs (PHP CodeSniffer checker) tool configured for PSR-12 standards to validate code style and detect style violations.

#### Scenario: Run CodeSniffer analysis
- **WHEN** a developer runs `make phpcs`
- **THEN** CodeSniffer analyzes all PHP files in `src/` and `tests/` directories
- **AND** CodeSniffer runs inside the Docker PHP container
- **AND** CodeSniffer validates code against PSR-12 coding standard
- **AND** the command outputs all detected style violations or confirms no violations found

#### Scenario: CodeSniffer detects style violations
- **WHEN** CodeSniffer analyzes code with style violations
- **THEN** CodeSniffer reports specific file locations, line numbers, and violation descriptions
- **AND** the exit code is non-zero to indicate failure
- **AND** developers can use the output to fix the issues manually or run `make phpcbf` to auto-fix

#### Scenario: CodeSniffer passes with no violations
- **WHEN** CodeSniffer analyzes code with no style violations
- **THEN** CodeSniffer reports success
- **AND** the exit code is zero
- **AND** the command completes successfully

### Requirement: PHP CodeSniffer Configuration
The system SHALL provide a PHP CodeSniffer configuration file that defines coding standard, paths, and validation rules.

#### Scenario: CodeSniffer configuration exists
- **WHEN** CodeSniffer is executed
- **THEN** it reads configuration from `phpcs.xml` or `phpcs.xml.dist`
- **AND** the configuration uses PSR-12 coding standard
- **AND** the configuration includes `src/` and `tests/` directories for analysis
- **AND** the configuration aligns with PSR-12 Extended Coding Style Guide

### Requirement: Makefile Integration for Code Style Tools
The system SHALL provide Makefile targets to run phpcbf and phpcs.

#### Scenario: Execute phpcbf via Makefile
- **WHEN** a developer runs `make phpcbf`
- **THEN** phpcbf executes inside the Docker PHP container
- **AND** the command uses `docker-compose exec -T php` to run in non-interactive mode
- **AND** the output is displayed in the terminal
- **AND** files are modified in place to fix style violations

#### Scenario: Execute CodeSniffer via Makefile
- **WHEN** a developer runs `make phpcs`
- **THEN** CodeSniffer executes inside the Docker PHP container
- **AND** the command uses `docker-compose exec -T php` to run in non-interactive mode
- **AND** the output is displayed in the terminal
- **AND** the command exits with appropriate code (0 for success, non-zero for violations)

### Requirement: Pre-commit Hook Validation with Code Style Tools
The system SHALL run phpcs (CodeSniffer checker) analysis automatically before allowing code commits.

#### Scenario: Pre-commit hook runs phpcs
- **WHEN** a developer attempts to commit code
- **THEN** the pre-commit hook executes phpcs (CodeSniffer checker) analysis
- **AND** phpcs runs inside the Docker PHP container
- **AND** the hook waits for phpcs to complete before proceeding

#### Scenario: Pre-commit hook blocks commit on phpcs failure
- **WHEN** phpcs detects style violations during pre-commit hook execution
- **THEN** the commit is blocked
- **AND** an error message is displayed indicating CodeSniffer violations
- **AND** the developer must fix the style issues before committing (by running `make phpcbf` or manually)
- **AND** the exit code prevents the commit from proceeding

#### Scenario: Pre-commit hook allows commit on code style success
- **WHEN** phpcs passes with no violations during pre-commit hook execution
- **THEN** the commit proceeds to next validation steps
- **AND** other pre-commit hook tasks (like PHPStan, deptrack, tests, and OpenAPI generation) continue to execute
- **AND** the commit completes successfully if all checks pass

### Requirement: Pre-commit Hook Test Execution
The system SHALL run all tests automatically before allowing code commits to ensure no functionality is broken.

#### Scenario: Pre-commit hook runs all tests
- **WHEN** a developer attempts to commit code
- **THEN** the pre-commit hook executes all tests via `make test`
- **AND** tests run inside the Docker PHP container
- **AND** the hook waits for tests to complete before proceeding
- **AND** all unit tests and feature tests are executed

#### Scenario: Pre-commit hook blocks commit on test failure
- **WHEN** tests fail during pre-commit hook execution
- **THEN** the commit is blocked
- **AND** an error message is displayed indicating test failures
- **AND** the developer must fix failing tests before committing
- **AND** the exit code prevents the commit from proceeding

#### Scenario: Pre-commit hook allows commit on test success
- **WHEN** all tests pass during pre-commit hook execution
- **THEN** the commit proceeds normally
- **AND** other pre-commit hook tasks (like OpenAPI generation) continue to execute
- **AND** the commit completes successfully

### Requirement: Test Coverage Goals
The system SHALL maintain 100% test coverage for all code in the `src/` directory, ensuring every class, method, and line of code is covered by tests.

#### Scenario: Coverage threshold is 100%
- **WHEN** coverage reports are generated
- **THEN** the coverage threshold in `coverage_percent` file is set to 100.00
- **AND** the coverage check command (`make test-coverage-check`) validates that coverage meets or exceeds 100%
- **AND** CI/CD pipelines fail if coverage drops below 100%

#### Scenario: All source code is covered by tests
- **WHEN** test coverage is analyzed
- **THEN** all classes in `src/` directory have 100% method coverage
- **AND** all classes in `src/` directory have 100% line coverage
- **AND** all edge cases, error paths, and exception scenarios are tested
- **AND** no uncovered code exists in the `src/` directory

#### Scenario: Infrastructure layer has complete test coverage
- **WHEN** infrastructure layer code is tested
- **THEN** `OrderRepository` class has 100% method and line coverage
- **AND** `PDOFactory` class has 100% method and line coverage
- **AND** all repository methods, including error handling and transaction rollback scenarios, are tested
- **AND** all factory methods, including different DSN formats and configurations, are tested

#### Scenario: Presentation layer has complete test coverage
- **WHEN** presentation layer code is tested
- **THEN** `RequestDeserializer` class has 100% method and line coverage
- **AND** `RequestValueResolver` class has 100% method and line coverage
- **AND** all deserialization paths, including edge cases and error scenarios, are tested
- **AND** all value resolver scenarios, including type checking and exception handling, are tested

#### Scenario: Coverage analysis identifies gaps
- **WHEN** coverage is below 100%
- **THEN** XML coverage report (`coverage/clover.xml`) is generated with line-by-line coverage data
- **AND** coverage analysis tools parse XML report to identify specific uncovered classes, methods, and lines
- **AND** each uncovered line in XML has `count="0"` attribute indicating it needs test coverage
- **AND** developers can use XML coverage report for programmatic line-by-line analysis
- **AND** HTML coverage report remains available in `coverage/` directory for visual inspection
- **AND** coverage reports provide actionable information for achieving 100% coverage

#### Scenario: XML coverage report format
- **WHEN** coverage reports are generated
- **THEN** XML coverage report (`coverage/clover.xml`) is generated in Clover format
- **AND** XML report contains `<line>` elements with `num`, `type`, and `count` attributes for each line
- **AND** uncovered lines have `count="0"` attribute
- **AND** covered lines have `count="1"` or higher
- **AND** XML report can be parsed programmatically for line-by-line coverage analysis
- **AND** HTML coverage report is also generated and remains in `coverage/` directory for visual inspection

### Requirement: Codeception Testing Framework
The system SHALL provide Codeception testing framework configured for running tests and generating coverage reports alongside PHPUnit.

#### Scenario: Codeception is installed
- **WHEN** dependencies are installed via Composer
- **THEN** Codeception is available as a dev dependency
- **AND** Codeception can be executed via `vendor/bin/codecept`
- **AND** Codeception version can be verified with `vendor/bin/codecept --version`

#### Scenario: Codeception configuration exists
- **WHEN** Codeception is executed
- **THEN** it reads configuration from `codeception.yml`
- **AND** the configuration specifies test directories and settings
- **AND** the configuration includes coverage settings
- **AND** Codeception is configured to work alongside PHPUnit

#### Scenario: Run tests with Codeception
- **WHEN** a developer runs Codeception tests
- **THEN** Codeception executes tests from configured test directories
- **AND** test results are displayed in the terminal
- **AND** Codeception can generate coverage reports

### Requirement: Test Coverage Report Generation
The system SHALL generate and persist test coverage reports when explicitly requested, and extract coverage percentage for display in documentation.

#### Scenario: Generate coverage reports
- **WHEN** a developer runs `make test-coverage`
- **THEN** test coverage reports are generated
- **AND** coverage reports are saved to `coverage/` directory
- **AND** HTML coverage reports are available for viewing
- **AND** XML coverage reports are generated for parsing

#### Scenario: Coverage reports persist
- **WHEN** coverage reports are generated
- **THEN** reports are saved to the `coverage/` directory
- **AND** the `coverage/` directory is gitignored
- **AND** reports remain available after test execution completes
- **AND** reports can be viewed in a web browser

#### Scenario: Extract coverage percentage
- **WHEN** coverage reports are generated
- **THEN** coverage percentage can be extracted from coverage XML report
- **AND** coverage percentage is available as a numeric value
- **AND** coverage percentage can be used to update documentation

#### Scenario: Display coverage in README
- **WHEN** coverage percentage is extracted
- **THEN** coverage percentage is displayed in README.md
- **AND** coverage information is updated when `make test-coverage` is run
- **AND** coverage percentage is clearly visible in the documentation

### Requirement: Makefile Coverage Command
The system SHALL provide a Makefile target that generates coverage reports and saves them properly.

#### Scenario: Execute coverage generation via Makefile
- **WHEN** a developer runs `make test-coverage`
- **THEN** coverage reports are generated inside the Docker PHP container
- **AND** the `coverage/` directory is created if it doesn't exist
- **AND** coverage reports are saved to the `coverage/` directory
- **AND** the command completes successfully with coverage data available

#### Scenario: Coverage command handles directory creation
- **WHEN** `make test-coverage` is run and `coverage/` directory doesn't exist
- **THEN** the directory is created automatically
- **AND** coverage reports are saved successfully
- **AND** no errors occur due to missing directory

