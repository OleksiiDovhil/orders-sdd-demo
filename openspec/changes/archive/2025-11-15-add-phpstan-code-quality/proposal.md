# Change: Add PHPStan Static Analysis Tool

## Why
Improve code quality and catch potential bugs early by introducing PHPStan static analysis at level 9. This will help maintain type safety, detect common errors, and ensure code consistency across the codebase. Integrating PHPStan into the development workflow via Makefile and pre-commit hooks ensures that all committed code meets quality standards.

## What Changes
- **ADDED**: PHPStan static analysis tool at level 9 (maximum strictness)
- **ADDED**: PHPStan configuration file (`phpstan.neon` or `phpstan.dist.neon`)
- **ADDED**: PHPStan execution command in Makefile (`make phpstan`)
- **ADDED**: PHPStan validation in pre-commit hook to prevent committing code with static analysis errors
- **MODIFIED**: All code files to fix PHPStan level 9 issues (or add appropriate ignores where necessary)
- **ADDED**: PHPStan to `composer.json` as a development dependency

## Impact
- **Affected specs**: New capability `code-quality`
- **Affected code**: 
  - New files: `phpstan.neon` (or `phpstan.dist.neon`), updated `.git/hooks/pre-commit`
  - Updated files: `composer.json`, `Makefile`, potentially all PHP files to fix issues
- **Developer workflow**: Developers must ensure PHPStan passes before committing code
- **No breaking changes**: This is a development tool addition that doesn't affect runtime behavior

