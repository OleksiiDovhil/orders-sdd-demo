# Symfony Application

A Symfony 7.3 application with Docker Compose local development environment.

## Prerequisites

- Docker and Docker Compose installed
- Make (optional, for convenience commands)

## Quick Start

### Using Docker (Recommended)

1. **Build the containers:**
   ```bash
   make build
   ```

2. **Start all services:**
   ```bash
   make up
   ```

3. **Install dependencies:**
   ```bash
   make exec ARGS="composer install"
   ```

4. **Access the application:**
   - Web: http://localhost:8080
   - PostgreSQL: localhost:5432

### Environment Variables

Create a `.env` file in the project root with the following variables:

```env
APP_ENV=dev
APP_SECRET=ThisTokenIsNotSoSecretChangeIt

# PostgreSQL Database Configuration
POSTGRES_DB=symfony
POSTGRES_USER=symfony
POSTGRES_PASSWORD=symfony
DATABASE_URL=postgresql://symfony:symfony@postgres:5432/symfony
```

## Makefile Commands

The project includes a Makefile with convenient commands for Docker operations:

- `make build` - Build Docker containers
- `make up` - Start all services in detached mode
- `make down` - Stop all services
- `make exec ARGS="command"` - Execute a command inside the PHP-FPM container
  - Example: `make exec ARGS="php bin/console cache:clear"`
  - Example: `make exec ARGS="composer install"`
- `make logs` - View container logs (follow mode)
- `make clean` - Remove containers and volumes (cleanup)

## Services

### PHP-FPM
- **Container**: `symfony-php`
- **Dockerfile**: `docker/php/Dockerfile`
- **Image**: PHP 8.4-FPM (Debian-based)
- **Extensions**: pdo_pgsql, pdo_mysql, mbstring, exif, pcntl, bcmath, gd
- **Composer**: Pre-installed

### Nginx
- **Container**: `symfony-nginx`
- **Port**: 8080 (mapped to container port 80)
- **Configuration**: `docker/nginx/nginx.conf`

### PostgreSQL
- **Container**: `symfony-postgres`
- **Version**: PostgreSQL 16
- **Port**: 5432
- **Database**: symfony (default)
- **User**: symfony (default)
- **Password**: symfony (default)
- **Data**: Persisted in Docker volume `postgres_data`

## Development Workflow

1. **Start services:**
   ```bash
   make up
   ```

2. **Install dependencies (first time):**
   ```bash
   make exec ARGS="composer install"
   ```

3. **Run Symfony commands:**
   ```bash
   make exec ARGS="php bin/console cache:clear"
   make exec ARGS="php bin/console doctrine:schema:create"
   ```

4. **View logs:**
   ```bash
   make logs
   ```

5. **Stop services:**
   ```bash
   make down
   ```

## Troubleshooting

### Port Already in Use
If port 8080 or 5432 is already in use, you can modify the port mappings in `docker-compose.yml`:
```yaml
ports:
  - "8081:80"  # Change 8080 to 8081
```

### Permission Issues
If you encounter permission issues with volumes, ensure your user has appropriate permissions or adjust the Dockerfile user settings.

### Database Connection Issues
- Verify PostgreSQL container is running: `docker-compose ps`
- Check database credentials in `.env` file
- Ensure `DATABASE_URL` uses `postgres` as the hostname (not `localhost`)

### Container Build Failures
- Clear Docker cache: `docker system prune -a`
- Rebuild without cache: `docker-compose build --no-cache`

### Volume Performance on macOS/Windows
Docker Desktop on macOS/Windows may have slower volume performance. This is expected and should not affect development significantly.

## Cleanup

To completely remove containers, volumes, and networks:
```bash
make clean
```

This will remove:
- All containers
- All volumes (including database data)
- All networks

**Warning**: This will delete your database data. Use with caution.

