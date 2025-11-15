# Change: Add PHP CS Fixer and phpcbf (PHP Code Beautifier and Fixer)

## Why
Enforce consistent code style across the codebase by adding PHP CS Fixer (automatic code formatting) and phpcbf (PHP Code Beautifier and Fixer, part of PHP CodeSniffer package) tools configured for PSR-12 standards. Both tools provide automatic code fixing capabilities to ensure all code follows the same formatting rules, improves code readability, and reduces style-related code review comments. Integrating these tools into the development workflow via Makefile and pre-commit hooks ensures that all committed code meets style standards automatically.

## What Changes
- **ADDED**: PHP CS Fixer tool for automatic code formatting
- **ADDED**: PHP CodeSniffer package (provides phpcbf for automatic fixing and phpcs for checking)
- **ADDED**: Configuration files for both tools (`.php-cs-fixer.php` or `.php-cs-fixer.dist.php`, `phpcs.xml` or `phpcs.xml.dist`)
- **ADDED**: Makefile targets to run CS Fixer and phpcbf (`make cs-fix`, `make cs-check`, `make phpcbf`, `make phpcs`)
- **ADDED**: CS Fixer and phpcbf/phpcs validation in pre-commit hook
- **ADDED**: Test execution in pre-commit hook to ensure all tests pass before commit
- **MODIFIED**: All code files to comply with PSR-12 standards (via CS Fixer and phpcbf auto-fix, and manual fixes for remaining issues)
- **ADDED**: PHP CS Fixer and PHP CodeSniffer to `composer.json` as development dependencies

## Impact
- **Affected specs**: Modified capability `code-quality`
- **Affected code**: 
  - New files: `.php-cs-fixer.php` (or `.php-cs-fixer.dist.php`), `phpcs.xml` (or `phpcs.xml.dist`), updated `.git/hooks/pre-commit`
  - Updated files: `composer.json`, `Makefile`, potentially all PHP files to fix style issues
- **Developer workflow**: Developers must ensure CS Fixer and phpcbf/phpcs pass before committing code, and all tests must pass
- **No breaking changes**: This is a development tool addition that doesn't affect runtime behavior

