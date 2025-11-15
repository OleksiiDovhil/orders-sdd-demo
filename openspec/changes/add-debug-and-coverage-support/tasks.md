## 1. Xdebug Configuration
- [ ] 1.1 Install Xdebug extension in `docker/php/Dockerfile`
- [ ] 1.2 Configure Xdebug in PHP configuration separate config file
- [ ] 1.3 Add Xdebug environment variables to `docker-compose.yml` for configuration off by default
- [ ] 1.4 Test Xdebug installation by checking `php -m | grep xdebug` in container
- [ ] 1.5 Verify Xdebug configuration with `php -i | grep xdebug`

## 2. Codeception Integration
- [ ] 2.1 Add Codeception to `composer.json` as dev dependency
- [ ] 2.2 Run `composer install` to install Codeception
- [ ] 2.3 Create `codeception.yml` configuration file
- [ ] 2.4 Configure Codeception to use PHPUnit as test runner
- [ ] 2.5 Configure Codeception coverage settings
- [ ] 2.6 Test Codeception installation with `vendor/bin/codecept --version`

## 3. Coverage Report Fixes
- [ ] 3.1 Update `Makefile` `test-coverage` target to ensure coverage directory exists
- [ ] 3.2 Fix coverage report generation to save results properly
- [ ] 3.3 Add `.gitignore` entry for `coverage/` directory if not present
- [ ] 3.4 Test coverage report generation with `make test-coverage`
- [ ] 3.5 Verify coverage HTML reports are generated in `coverage/` directory

## 4. Coverage Percentage Extraction
- [ ] 4.1 Add Makefile target to extract coverage percentage from PHPUnit XML report
- [ ] 4.2 Create script or Makefile command to parse coverage XML and extract percentage
- [ ] 4.3 Test coverage percentage extraction
- [ ] 4.4 Add target to update README.md with coverage percentage

## 5. README Update
- [ ] 5.1 Add coverage section to README.md
- [ ] 5.2 Add coverage percentage badge or text display
- [ ] 5.3 Document how to generate coverage reports
- [ ] 5.4 Document Xdebug debugging setup

## 6. Test Execution and Validation
- [ ] 6.1 Run PHPStan for src folder: `make phpstan-src` and fix any issues found
- [ ] 6.2 Run CodeSniffer for src folder: `make phpcbf-src` to auto-fix issues, then `make phpcs-src` to verify
- [ ] 6.3 Fix any remaining CodeSniffer violations in src folder that phpcbf could not auto-fix
- [ ] 6.4 Run deptrac: `make deptrack` and fix any architectural violations found
- [ ] 6.5 Run all tests: `make test` and verify all tests pass with exit code 0
- [ ] 6.6 Fix any failing tests that may have been broken by code changes
- [ ] 6.7 Run PHPStan globally (with tests folder): `make phpstan` and fix any issues found
- [ ] 6.8 Run CodeSniffer globally (with tests folder): `make phpcbf` to auto-fix issues, then `make phpcs` to verify
- [ ] 6.9 Fix any remaining CodeSniffer violations that phpcbf could not auto-fix
- [ ] 6.10 Verify test coverage is maintained after code changes
- [ ] 6.11 Test Xdebug debugging functionality with a simple PHP script
- [ ] 6.12 Verify Codeception can run tests and generate coverage reports

