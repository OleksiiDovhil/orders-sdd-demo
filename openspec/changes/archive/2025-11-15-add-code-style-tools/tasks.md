## 1. Add PHP CodeSniffer Dependencies
- [x] 1.1 Add `squizlabs/php_codesniffer` to `composer.json` as a development dependency
- [x] 1.2 Run `docker-compose exec php composer update` to install the new dependencies

## 2. Configure PHP CodeSniffer (phpcs and phpcbf)
- [x] 2.1 Create `phpcs.xml` or `phpcs.xml.dist` configuration file
- [x] 2.2 Configure CodeSniffer to use PSR-12 coding standard
- [x] 2.3 Configure CodeSniffer to analyze `src/` and `tests/` directories
- [x] 2.4 Test phpcs (checker) configuration by running it manually
- [x] 2.5 Test phpcbf (fixer) configuration by running it manually

## 3. Add Makefile Targets
- [x] 3.1 Add `make phpcbf` target to run phpcbf (PHP Code Beautifier and Fixer) inside Docker container
- [x] 3.2 Add `make phpcs` target to run phpcs (PHP CodeSniffer checker) inside Docker container
- [x] 3.3 Test all Makefile targets work correctly

## 4. Run phpcbf to Fix Issues
- [x] 4.1 Run `make phpcbf` to automatically fix all fixable code style issues with phpcbf
- [x] 4.2 Review the changes made by phpcbf
- [x] 4.3 Manually fix any issues that phpcbf could not automatically fix
- [x] 4.4 Run `make phpcs` to verify CodeSniffer passes (no remaining violations)
- [x] 4.5 Commit the style fixes (if any were made)

## 5. Update Pre-commit Hook
- [x] 5.1 Update `.git/hooks/pre-commit` to run phpcs (CodeSniffer checker) before allowing commits
- [x] 5.2 Update `.git/hooks/pre-commit` to run all tests (`make test`) before allowing commits
- [x] 5.3 Ensure pre-commit hook runs tools in correct order:
  1. phpcs (CodeSniffer checker)
  2. PHPStan (already exists)
  3. Deptrack (already exists)
  4. Tests
  5. OpenAPI generation (already exists)
- [x] 5.4 Test that pre-commit hook prevents commits when phpcs fails
- [x] 5.5 Test that pre-commit hook prevents commits when tests fail
- [x] 5.6 Test that pre-commit hook allows commits when all checks pass

## 6. Test Execution and Validation
- [x] 6.1 Run all unit tests: `make test-unit` and verify they pass
- [x] 6.2 Run all feature tests: `make test-feature` and verify they pass
- [x] 6.3 Run all tests: `make test` and verify all tests pass with exit code 0
- [x] 6.4 Fix any failing tests that may have been broken by code style changes
- [x] 6.5 Verify test coverage is maintained after code changes
- [x] 6.6 Run `make phpcs` and verify no CodeSniffer style violations remain
- [x] 6.7 Run `make phpstan` and verify static analysis still passes
- [x] 6.8 Run `make deptrack` and verify dependency analysis still passes
