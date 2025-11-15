## 1. Add PHP CS Fixer and CodeSniffer Dependencies
- [ ] 1.1 Add `friendsofphp/php-cs-fixer` to `composer.json` as a development dependency
- [ ] 1.2 Add `squizlabs/php_codesniffer` to `composer.json` as a development dependency
- [ ] 1.3 Run `docker-compose exec php composer update` to install the new dependencies

## 2. Configure PHP CS Fixer
- [ ] 2.1 Create `.php-cs-fixer.php` or `.php-cs-fixer.dist.php` configuration file
- [ ] 2.2 Configure CS Fixer to use PSR-12 ruleset
- [ ] 2.3 Configure CS Fixer to analyze `src/` and `tests/` directories
- [ ] 2.4 Configure CS Fixer to use 4 spaces for indentation
- [ ] 2.5 Configure CS Fixer to enforce strict types declaration
- [ ] 2.6 Configure CS Fixer to set line length limit to 120 characters
- [ ] 2.7 Test CS Fixer configuration by running it manually

## 3. Configure PHP CodeSniffer (phpcs and phpcbf)
- [ ] 3.1 Create `phpcs.xml` or `phpcs.xml.dist` configuration file
- [ ] 3.2 Configure CodeSniffer to use PSR-12 coding standard
- [ ] 3.3 Configure CodeSniffer to analyze `src/` and `tests/` directories
- [ ] 3.4 Test phpcs (checker) configuration by running it manually
- [ ] 3.5 Test phpcbf (fixer) configuration by running it manually

## 4. Add Makefile Targets
- [ ] 4.1 Add `make cs-fix` target to run PHP CS Fixer with `--fix` flag inside Docker container
- [ ] 4.2 Add `make cs-check` target to run PHP CS Fixer with `--dry-run` flag inside Docker container
- [ ] 4.3 Add `make phpcbf` target to run phpcbf (PHP Code Beautifier and Fixer) inside Docker container
- [ ] 4.4 Add `make phpcs` target to run phpcs (PHP CodeSniffer checker) inside Docker container
- [ ] 4.5 Test all Makefile targets work correctly

## 5. Run CS Fixer and phpcbf to Fix Issues
- [ ] 5.1 Run `make cs-fix` to automatically fix all fixable code style issues with CS Fixer
- [ ] 5.2 Run `make phpcbf` to automatically fix all fixable code style issues with phpcbf
- [ ] 5.3 Review the changes made by CS Fixer and phpcbf
- [ ] 5.4 Manually fix any issues that CS Fixer and phpcbf could not automatically fix
- [ ] 5.5 Run `make cs-check` to verify CS Fixer issues are resolved
- [ ] 5.6 Run `make phpcs` to verify CodeSniffer passes (no remaining violations)
- [ ] 5.7 Commit the style fixes (if any were made)

## 6. Update Pre-commit Hook
- [ ] 6.1 Update `.git/hooks/pre-commit` to run CS Fixer check before allowing commits
- [ ] 6.2 Update `.git/hooks/pre-commit` to run phpcs (CodeSniffer checker) before allowing commits
- [ ] 6.3 Update `.git/hooks/pre-commit` to run all tests (`make test`) before allowing commits
- [ ] 6.4 Ensure pre-commit hook runs tools in correct order:
  1. CS Fixer check
  2. phpcs (CodeSniffer checker)
  3. PHPStan (already exists)
  4. Deptrack (already exists)
  5. Tests
  6. OpenAPI generation (already exists)
- [ ] 6.5 Test that pre-commit hook prevents commits when CS Fixer check fails
- [ ] 6.6 Test that pre-commit hook prevents commits when phpcs fails
- [ ] 6.7 Test that pre-commit hook prevents commits when tests fail
- [ ] 6.8 Test that pre-commit hook allows commits when all checks pass

## 7. Test Execution and Validation
- [ ] 7.1 Run all unit tests: `make test-unit` and verify they pass
- [ ] 7.2 Run all feature tests: `make test-feature` and verify they pass
- [ ] 7.3 Run all tests: `make test` and verify all tests pass with exit code 0
- [ ] 7.4 Fix any failing tests that may have been broken by code style changes
- [ ] 7.5 Verify test coverage is maintained after code changes
- [ ] 7.6 Run `make cs-check` and verify no CS Fixer style issues remain
- [ ] 7.7 Run `make phpcs` and verify no CodeSniffer style violations remain
- [ ] 7.8 Run `make phpstan` and verify static analysis still passes
- [ ] 7.9 Run `make deptrack` and verify dependency analysis still passes

