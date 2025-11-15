# Change: Add Docker Compose Local Development Environment

## Why
Enable developers to run the Symfony application locally using Docker Compose, providing a consistent development environment with all required services (PHP-FPM, PostgreSQL, Nginx) containerized. This eliminates environment setup inconsistencies and simplifies onboarding.

## What Changes
- **ADDED**: Docker Compose configuration with PHP-FPM, PostgreSQL, and Nginx services
- **ADDED**: Makefile with build and exec commands for container management
- **ADDED**: Dockerfile for PHP-FPM based on latest Debian standard image
- **ADDED**: Nginx configuration for serving the Symfony application
- **ADDED**: PostgreSQL service configuration
- **ADDED**: Environment variable configuration for Docker services
- **ADDED**: Documentation for local development setup

## Impact
- **Affected specs**: New capability `local-development`
- **Affected code**: 
  - New files: `docker-compose.yml`, `Dockerfile`, `Makefile`, `docker/nginx/nginx.conf`, `.env.docker`
  - Updated files: `.gitignore` (add Docker-related entries if needed)
- **Developer workflow**: Developers can now use `make build` and `make exec` commands instead of manual setup
- **No breaking changes**: This is additive infrastructure

