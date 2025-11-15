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
The system SHALL run PHPStan analysis automatically before allowing code commits.

#### Scenario: Pre-commit hook runs PHPStan
- **WHEN** a developer attempts to commit code
- **THEN** the pre-commit hook executes PHPStan analysis
- **AND** PHPStan runs inside the Docker PHP container
- **AND** the hook waits for PHPStan to complete before proceeding

#### Scenario: Pre-commit hook blocks commit on PHPStan failure
- **WHEN** PHPStan detects errors during pre-commit hook execution
- **THEN** the commit is blocked
- **AND** an error message is displayed indicating PHPStan failures
- **AND** the developer must fix the issues before committing
- **AND** the exit code prevents the commit from proceeding

#### Scenario: Pre-commit hook allows commit on PHPStan success
- **WHEN** PHPStan passes with no errors during pre-commit hook execution
- **THEN** the commit proceeds normally
- **AND** other pre-commit hook tasks (like OpenAPI generation) continue to execute
- **AND** the commit completes successfully

