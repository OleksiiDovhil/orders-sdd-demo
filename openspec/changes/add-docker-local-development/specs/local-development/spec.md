## ADDED Requirements

### Requirement: Docker Compose Local Development Environment
The system SHALL provide a Docker Compose-based local development environment that includes PHP-FPM, PostgreSQL, and Nginx services, enabling developers to run the application with a single command.

#### Scenario: Build development environment
- **WHEN** a developer runs `make build`
- **THEN** all Docker containers (PHP-FPM, PostgreSQL, Nginx) are built and configured
- **AND** the PHP-FPM container uses the latest Debian-based official PHP 8.4-FPM image from Docker Hub
- **AND** all required PHP extensions are installed
- **AND** Composer is available in the PHP-FPM container

#### Scenario: Execute commands in PHP-FPM container
- **WHEN** a developer runs `make exec [command]`
- **THEN** the specified command executes inside the PHP-FPM container
- **AND** the command has access to the application source code
- **AND** Composer and Symfony console are available

#### Scenario: Access application via Nginx
- **WHEN** all containers are running
- **THEN** the Symfony application is accessible via Nginx
- **AND** Nginx proxies requests to PHP-FPM
- **AND** static files are served directly by Nginx
- **AND** PHP files are processed by PHP-FPM

#### Scenario: Database connectivity
- **WHEN** the application runs in Docker
- **THEN** it can connect to the PostgreSQL container
- **AND** database credentials are configured via environment variables
- **AND** database data persists across container restarts via volumes

#### Scenario: Source code changes reflect immediately
- **WHEN** a developer modifies source code files
- **THEN** changes are immediately available in the running container
- **AND** no container rebuild is required for code changes
- **AND** vendor directory is properly handled (mounted or ignored as appropriate)

### Requirement: Makefile Commands
The system SHALL provide Makefile commands for common Docker operations.

#### Scenario: Build containers
- **WHEN** a developer runs `make build`
- **THEN** Docker Compose builds all required containers
- **AND** containers are configured with proper networking and volumes

#### Scenario: Execute in container
- **WHEN** a developer runs `make exec [command]`
- **THEN** the command executes in the PHP-FPM container
- **AND** if no command is provided, an interactive shell is opened
- **AND** the working directory is set to the application root

#### Scenario: Start services
- **WHEN** a developer runs `make up` or `make start`
- **THEN** all Docker Compose services start
- **AND** services are accessible on configured ports

#### Scenario: Stop services
- **WHEN** a developer runs `make down` or `make stop`
- **THEN** all Docker Compose services stop
- **AND** containers are removed (or kept running based on command)

#### Scenario: View logs
- **WHEN** a developer runs `make logs [service]`
- **THEN** container logs are displayed
- **AND** if a service name is provided, only that service's logs are shown

### Requirement: PHP-FPM Container Configuration
The PHP-FPM container SHALL be based on the official Debian PHP-FPM image and configured for Symfony development.

#### Scenario: PHP version and extensions
- **WHEN** the PHP-FPM container is built
- **THEN** PHP 8.4 is installed
- **AND** required extensions are available (pdo_pgsql, ctype, iconv, etc.)
- **AND** PHP-FPM is configured and running

#### Scenario: Composer availability
- **WHEN** commands are executed in the PHP-FPM container
- **THEN** Composer is available in the PATH
- **AND** Composer can install and update dependencies

#### Scenario: Working directory
- **WHEN** commands execute in the PHP-FPM container
- **THEN** the working directory is set to `/var/www/html` or appropriate application root
- **AND** source code is accessible at the working directory

### Requirement: PostgreSQL Container Configuration
The system SHALL provide a PostgreSQL container for database operations.

#### Scenario: Database service availability
- **WHEN** containers are started
- **THEN** PostgreSQL service is running
- **AND** database is accessible from PHP-FPM container
- **AND** connection credentials are configurable via environment variables

#### Scenario: Data persistence
- **WHEN** database data is created
- **THEN** data persists in a Docker volume
- **AND** data remains available after container restart
- **AND** data can be cleared by removing the volume

### Requirement: Nginx Container Configuration
The system SHALL provide an Nginx container that serves the Symfony application.

#### Scenario: Request proxying
- **WHEN** a request is made to Nginx
- **THEN** PHP requests are proxied to PHP-FPM
- **AND** static files are served directly by Nginx
- **AND** proper headers are set for Symfony

#### Scenario: Configuration management
- **WHEN** Nginx container starts
- **THEN** custom Nginx configuration is loaded from `docker/nginx/nginx.conf`
- **AND** configuration supports Symfony's front controller pattern
- **AND** error and access logs are properly configured

