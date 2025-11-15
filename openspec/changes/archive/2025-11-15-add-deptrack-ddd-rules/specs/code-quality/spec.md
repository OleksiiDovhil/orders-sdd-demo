## ADDED Requirements

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

