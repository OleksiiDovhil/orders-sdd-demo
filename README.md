## Orders API

This feature introduces three new API endpoints:

1. **Create Order**
2. **Complete Order**
3. **Get Recent Orders**

All data storage must use **PDO** and the **Repository** pattern.

---

### 1. Create Order (DONE)

Creates a new order.

#### Request body

```jsonc
{
  "sum": 1000,              // total order amount
  "contractorType": 1,      // contractor type (e.g. 1 = individual, 2 = legal entity)
  "items": [
    {
      "productId": 1,       // product ID
      "price": 1000,        // price per unit
      "quantity": 1         // quantity
    }
    // ...
  ]
}
```

#### Order number format

On creation, the system generates a unique order number in the format:

```text
{year}-{month}-{sequentialOrderNumber}
```

Example:

```text
2020-09-12345
```

#### Redirect behavior

After a successful order creation:

* For **individuals (physical persons)**: redirect to
  `http://some-pay-agregator.com/pay/{orderNumber}`

* For **legal entities**: redirect to
  `http://some-pay-agregator.com/orders/{orderNumber}/bill`

---

### 2. Complete Order (DONE)

Checks the payment status of an order and returns a result that allows the frontend to show either:

* a **“thank you” page** (if paid), or
* a **payment reminder** (if not paid).

#### Payment status check logic

* **Legal entities**
  Payment status is checked via a **separate microservice**.

* **Individuals (physical persons)**
  Payment status is read from a **payment flag in the database**.
  This flag is updated by an external microservice (the implementation of this data exchange is out of scope for this feature).

---

### 3. Get Recent Orders

Returns information about the specified number of most recent orders.

> The endpoint must accept a parameter that defines how many last orders to return (e.g. `limit`).

#### Response body

```jsonc
[
  {
    "id": "2020-09-123456",   // order number
    "sum": 1000,              // total order amount
    "contractorType": 1,      // contractor type (individual / legal entity)
    "items": [
      {
        "productId": 1,       // product ID
        "price": 1000,        // price per unit
        "quantity": 1         // quantity
      }
      // ...
    ]
  }
  // ...
]
```

---

### Technical Requirements

* Use **PDO** for all database access.
* Implement data access via the **Repository** pattern.



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

# Payment Aggregator Configuration
PAYMENT_AGGREGATOR_BASE_URL=http://some-pay-agregator.com
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

