.PHONY: build up down exec logs clean

# Build Docker containers
build:
	docker-compose build

# Start all services
up:
	docker-compose up -d

# Stop all services
down:
	docker-compose down

# Execute command in PHP-FPM container
exec:
	docker-compose exec php $(ARGS)

# View container logs
logs:
	docker-compose logs -f

# Remove containers and volumes
clean:
	docker-compose down -v
	docker-compose rm -f

