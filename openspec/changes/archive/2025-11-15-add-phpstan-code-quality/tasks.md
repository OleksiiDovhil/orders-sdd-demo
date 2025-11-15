## 1. Setup PHPStan
- [x] 1.1 Add PHPStan to `composer.json` as a development dependency (version compatible with PHP 8.4)
- [x] 1.2 Install PHPStan via Composer
- [x] 1.3 Create PHPStan configuration file (`phpstan.neon` or `phpstan.dist.neon`) with level 9
- [x] 1.4 Configure PHPStan to analyze `src/` and `tests/` directories
- [x] 1.5 Configure PHPStan to work with Symfony and PHPUnit (add necessary extensions if needed)

## 2. Integration with Build System
- [x] 2.1 Add `phpstan` target to Makefile that runs PHPStan analysis
- [x] 2.2 Ensure PHPStan runs inside Docker container (using `docker-compose exec -T php`)
- [x] 2.3 Test that `make phpstan` command works correctly

## 3. Fix Static Analysis Issues
- [x] 3.1 Run PHPStan initially to identify all issues
- [x] 3.2 Fix all PHPStan level 9 errors in the codebase
- [x] 3.3 For issues that cannot be fixed after 10 attempts, add appropriate PHPStan ignore annotations or configuration
- [x] 3.4 Verify that PHPStan passes with zero errors after fixes

## 4. Pre-commit Hook Integration
- [x] 4.1 Update `.git/hooks/pre-commit` to run PHPStan before allowing commits
- [x] 4.2 Ensure PHPStan runs in Docker container within the hook
- [x] 4.3 Configure hook to exit with error code if PHPStan fails
- [x] 4.4 Test that pre-commit hook prevents commits when PHPStan fails
- [x] 4.5 Test that pre-commit hook allows commits when PHPStan passes

## 5. Test Execution and Validation
- [x] 5.1 Run all unit tests: `make test-unit` and verify they pass
- [x] 5.2 Run all feature tests: `make test-feature` and verify they pass
- [x] 5.3 Run all tests: `make test` and verify all tests pass with exit code 0
- [x] 5.4 Fix any failing tests that may have been broken by PHPStan fixes
- [x] 5.5 Verify test coverage is maintained after code changes

## 6. Final Validation
- [x] 6.1 Run `make phpstan` and verify it passes
- [x] 6.2 Attempt to commit code with PHPStan errors and verify it's blocked
- [x] 6.3 Commit code with all PHPStan issues fixed and verify it succeeds
- [x] 6.4 Verify all existing tests still pass after PHPStan fixes

