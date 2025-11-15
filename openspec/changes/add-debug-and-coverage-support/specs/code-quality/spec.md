## ADDED Requirements
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

