# Change: Add Debug and Coverage Support

## Why
Developers need the ability to debug PHP scripts (especially tests) using Xdebug in the Docker environment. Additionally, the project needs comprehensive test coverage reporting with Codeception integration, and coverage results should be automatically saved and displayed in the README.

## What Changes
- Add Xdebug extension to PHP Docker container with proper configuration for debugging
- Add Codeception testing framework with coverage support
- Fix Makefile `test-coverage` target to properly save coverage results
- Add automated coverage percentage extraction and update README.md with coverage badge/percentage

## Impact
- Affected specs: `local-development` (Docker/Xdebug configuration), `code-quality` (coverage reporting)
- Affected code: 
  - `docker/php/Dockerfile` (Xdebug installation)
  - `docker-compose.yml` (Xdebug environment variables)
  - `Makefile` (coverage command fixes)
  - `composer.json` (Codeception dependency)
  - `README.md` (coverage percentage display)
  - `codeception.yml` (new Codeception configuration)

