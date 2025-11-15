## Context
The project needs a consistent local development environment that matches production-like conditions. Currently, developers must set up PHP, PostgreSQL, and Nginx manually, leading to environment inconsistencies and onboarding friction.

## Goals / Non-Goals

### Goals
- Provide one-command setup for local development (`make build`)
- Use official, standard Docker images from Docker Hub
- Support all Symfony application requirements (PHP 8.4, PostgreSQL, Nginx)
- Enable easy container interaction via Makefile commands
- Maintain development speed with volume mounts for live code changes

### Non-Goals
- Production deployment configuration (separate concern)
- Kubernetes or orchestration setup
- Multi-environment Docker Compose files (single dev setup)
- Docker Swarm or advanced orchestration

## Decisions

### Decision: Use Official Debian PHP-FPM Image
- **What**: Use `php:8.4-fpm` (latest Debian-based) from Docker Hub
- **Why**: 
  - Official images are well-maintained and secure
  - Debian base provides stability and compatibility
  - PHP-FPM variant is appropriate for Nginx setup
  - Matches project PHP 8.4 requirement
- **Alternatives considered**:
  - Alpine-based images: Rejected due to potential compatibility issues with some PHP extensions
  - Custom base image: Rejected as unnecessary complexity

### Decision: Separate Nginx Container
- **What**: Run Nginx in separate container, not integrated with PHP-FPM
- **Why**:
  - Follows single responsibility principle
  - Easier to configure and maintain
  - Allows independent scaling if needed
  - Standard Docker Compose pattern
- **Alternatives considered**:
  - Integrated PHP-FPM/Nginx: Rejected as less flexible

### Decision: Makefile for Common Commands
- **What**: Use Makefile to wrap Docker Compose commands
- **Why**:
  - Provides simple, memorable commands (`make build`, `make exec`)
  - Abstracts Docker Compose complexity
  - Consistent interface across team
  - Easy to extend with additional commands
- **Alternatives considered**:
  - Shell scripts: Rejected as Makefile is more standard for build tasks
  - Direct docker-compose commands: Rejected as too verbose

### Decision: Volume Mounts for Source Code
- **What**: Mount `src/`, `config/`, `public/` directories as volumes
- **Why**:
  - Enables live code changes without container rebuild
  - Faster development iteration
  - Matches common Docker development practices
- **Alternatives considered**:
  - Copy files into image: Rejected as requires rebuild for every change

### Decision: PostgreSQL as Separate Service
- **What**: Use official `postgres` image as separate service
- **Why**:
  - Standard, well-maintained image
  - Easy to configure and reset
  - Persistent data via volumes
- **Alternatives considered**:
  - MySQL/MariaDB: Rejected as project specifies PostgreSQL

## Risks / Trade-offs

### Risk: Volume Performance on macOS/Windows
- **Mitigation**: Use Docker Desktop with optimized volume mounting, document performance considerations

### Risk: Port Conflicts
- **Mitigation**: Use non-standard ports (e.g., 8080 for Nginx) or document port requirements

### Risk: Environment Variable Management
- **Mitigation**: Use `.env.docker` file with clear documentation, separate from application `.env`

### Trade-off: Container Build Time vs. Development Speed
- **Decision**: Optimize for development speed with volume mounts, accept slower initial build

## Migration Plan

### Steps
1. Create Docker configuration files
2. Add Makefile commands
3. Document setup process in README
4. Test with fresh clone of repository
5. Update onboarding documentation

### Rollback
- Remove Docker files if issues arise
- Developers can continue using local PHP/PostgreSQL setup
- No breaking changes to existing codebase

## Open Questions
- Should we include Redis or other services? (Defer to future change if needed)
- Should we add health checks? (Consider for future improvement)
- Should we version-lock Docker images? (Use latest for now, can pin later)

