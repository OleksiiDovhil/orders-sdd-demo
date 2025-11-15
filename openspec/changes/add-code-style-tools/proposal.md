# Change: Add phpcbf and phpcs (PHP Code Beautifier and Fixer / PHP CodeSniffer)

## Why
Enforce consistent code style across the codebase by adding phpcbf (PHP Code Beautifier and Fixer) and phpcs (PHP CodeSniffer checker), both part of the PHP CodeSniffer package, configured for PSR-12 standards. These tools provide automatic code fixing and validation capabilities to ensure all code follows the same formatting rules, improves code readability, and reduces style-related code review comments. Integrating these tools into the development workflow via Makefile and pre-commit hooks ensures that all committed code meets style standards automatically.

## What Changes
- **ADDED**: PHP CodeSniffer package (provides phpcbf for automatic fixing and phpcs for checking)
- **ADDED**: Configuration file for CodeSniffer (`phpcs.xml` or `phpcs.xml.dist`)
- **ADDED**: Makefile targets to run phpcbf and phpcs (`make phpcbf`, `make phpcs`)
- **ADDED**: phpcbf/phpcs validation in pre-commit hook
- **ADDED**: Test execution in pre-commit hook to ensure all tests pass before commit
- **MODIFIED**: All code files to comply with PSR-12 standards (via phpcbf auto-fix, and manual fixes for remaining issues)
- **ADDED**: PHP CodeSniffer to `composer.json` as a development dependency

## Impact
- **Affected specs**: Modified capability `code-quality`
- **Affected code**: 
  - New files: `phpcs.xml` (or `phpcs.xml.dist`), updated `.git/hooks/pre-commit`
  - Updated files: `composer.json`, `Makefile`, potentially all PHP files to fix style issues
- **Developer workflow**: Developers must ensure phpcbf/phpcs pass before committing code, and all tests must pass
- **No breaking changes**: This is a development tool addition that doesn't affect runtime behavior

