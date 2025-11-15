## 1. Docker Configuration
- [x] 1.1 Create `docker-compose.yml` with PHP-FPM, PostgreSQL, and Nginx services
- [x] 1.2 Create `Dockerfile` for PHP-FPM using latest Debian standard image from Docker Hub
- [x] 1.3 Create `docker/nginx/nginx.conf` configuration file
- [x] 1.4 Create `.env.docker` or update `.env` with Docker-specific environment variables
- [x] 1.5 Configure volume mounts for source code and vendor directory
- [x] 1.6 Configure network for service communication

## 2. Makefile Commands
- [x] 2.1 Create `Makefile` with `build` command to build Docker containers
- [x] 2.2 Add `exec` command to execute commands inside PHP-FPM container
- [x] 2.3 Add `up` command to start all services
- [x] 2.4 Add `down` command to stop all services
- [x] 2.5 Add `logs` command to view container logs
- [x] 2.6 Add `clean` command to remove containers and volumes

## 3. PHP-FPM Container Setup
- [x] 3.1 Configure PHP-FPM with required PHP extensions (pdo_pgsql, etc.)
- [x] 3.2 Install Composer in container
- [x] 3.3 Set up proper working directory
- [x] 3.4 Configure PHP-FPM pool settings

## 4. PostgreSQL Container Setup
- [x] 4.1 Configure PostgreSQL service with database name, user, and password
- [x] 4.2 Set up persistent volume for database data
- [x] 4.3 Configure connection settings in environment variables

## 5. Nginx Container Setup
- [x] 5.1 Configure Nginx to proxy requests to PHP-FPM
- [x] 5.2 Set up proper document root and index files
- [x] 5.3 Configure PHP-FPM upstream connection
- [x] 5.4 Set up proper error and access logging

## 6. Documentation
- [x] 6.1 Create or update README with Docker setup instructions
- [x] 6.2 Document all Makefile commands and their usage
- [x] 6.3 Add troubleshooting section for common Docker issues

## 7. Testing
- [x] 7.1 Verify `make build` successfully builds all containers
- [x] 7.2 Verify `make exec` allows executing commands in PHP-FPM container
- [x] 7.3 Verify application is accessible via Nginx
- [x] 7.4 Verify PostgreSQL connection from PHP application
- [x] 7.5 Test Composer install/update commands inside container

