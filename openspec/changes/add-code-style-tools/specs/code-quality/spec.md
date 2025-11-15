## ADDED Requirements

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

## MODIFIED Requirements

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

