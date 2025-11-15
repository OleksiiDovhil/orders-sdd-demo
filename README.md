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
- `make test` - Run all tests
- `make test-coverage` - Run tests with coverage report (generates HTML and XML reports in `coverage/` directory)
- `make test-coverage-percent` - Extract and display coverage percentage from the latest coverage report
- `make test-coverage-check` - Run tests with coverage and verify coverage meets 100% threshold
- `make test-coverage-analyze` - Analyze coverage report to identify uncovered code (useful when coverage decreased)
- `make test-coverage-auto-fix` - Automated workflow: runs coverage, checks 100% threshold, prioritizes recently modified files, and guides test creation

## Services

### PHP-FPM
- **Container**: `symfony-php`
- **Dockerfile**: `docker/php/Dockerfile`
- **Image**: PHP 8.4-FPM (Debian-based)
- **Extensions**: pdo_pgsql, pdo_mysql, mbstring, exif, pcntl, bcmath, gd, xdebug
- **Composer**: Pre-installed
- **Xdebug**: Installed and configured (disabled by default, see Debugging section)

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

## Testing and Coverage

### Running Tests

Run all tests:
```bash
make test
```

Run tests with coverage report:
```bash
make test-coverage
```

**Note**: Coverage reports require Xdebug to be installed. If you see "No code coverage driver available", rebuild the container:
```bash
make build
make up
```

This generates:
- HTML coverage report in `coverage/` directory (accessible at http://localhost:8080/coverage/)
- XML coverage report in `coverage/clover.xml` (for CI/CD integration)
- Text coverage report in `coverage/coverage.txt` (easy to parse, shows method-level coverage)

Extract coverage percentage (requires coverage report to be generated first):
```bash
make test-coverage-percent
```

**Note**: You must run `make test-coverage` first to generate the coverage report before extracting the percentage.

Run tests with coverage and check if coverage meets the 100% threshold:
```bash
make test-coverage-check
```

This will:
1. Run tests with coverage
2. Compare current coverage with the 100% threshold
3. Exit with error code if coverage is below 100%

This is useful for CI/CD pipelines to ensure coverage doesn't decrease below 100%.

Analyze coverage report to identify uncovered code (useful when coverage is below 100%):
```bash
make test-coverage-analyze
```

This will:
1. Compare current coverage with the 100% threshold
2. If coverage is below 100%, analyze the coverage report to find:
   - Classes with uncovered code (sorted by coverage percentage)
   - Uncovered methods in each class
   - Uncovered line counts
3. Display a prioritized list of classes and methods that need test coverage
4. Provide recommendations for improving coverage

**Automated coverage fix workflow** (recommended):
```bash
make test-coverage-auto-fix
```

This automated workflow will:
1. **Run tests with coverage** - Generates all coverage reports (HTML, XML, text)
2. **Check coverage threshold** - Compares current coverage with 100% threshold
3. **If coverage is below 100%**:
   - Analyzes both `coverage.txt` and `clover.xml` to find uncovered code
   - **Prioritizes recently created/edited files** (from git diff) as **HIGHEST PRIORITY** ⭐
   - Sorts remaining files by coverage percentage (lowest first)
   - Provides actionable list with file paths and test locations
4. **Guides you to add tests** - Shows exactly which files to test and where to create test files
5. **Iterative process** - After adding tests, run `make test-coverage-check` to verify improvement

**Priority order:**
1. ⭐ **Recently modified files** (from git diff) - HIGHEST PRIORITY
2. Files with lowest coverage percentages
3. Files with most uncovered methods/lines

**Note**: The text coverage report (`coverage/coverage.txt`) provides method-level details that are easy to parse programmatically and is useful for Cursor agents to identify specific uncovered code.

### Pre-commit Coverage Check

The project includes a pre-commit hook that automatically checks test coverage before each commit:

- **If coverage is below 100%**: The commit is blocked with an error message showing how much coverage is below the threshold
- **If coverage is 100% or above**: The commit proceeds normally

The coverage check runs as part of the pre-commit hook after all other validations (CodeSniffer, PHPStan, Deptrack, and tests) have passed.

**Note**: The project requires 100% test coverage. All code in the `src/` directory must be covered by tests.

### Test Coverage

Current test coverage: **N/A** (run `make test-coverage` to generate coverage report)

Coverage reports are generated in the `coverage/` directory and are excluded from version control.

## Debugging with Xdebug

Xdebug is installed in the PHP container but **disabled by default** to avoid performance impact.

### Enable Xdebug

To enable Xdebug for debugging, set the `XDEBUG_MODE` environment variable:

1. **Option 1: Set in `.env` file** (recommended for persistent configuration):
   ```env
   XDEBUG_MODE=debug,coverage
   ```

2. **Option 2: Set when starting containers**:
   ```bash
   XDEBUG_MODE=debug,coverage docker-compose up -d
   ```

3. **Option 3: Set for a single command**:
   ```bash
   XDEBUG_MODE=debug,coverage make exec ARGS="php bin/console cache:clear"
   ```

### Xdebug Configuration

- **Client Host**: `host.docker.internal` (for Docker Desktop)
- **Client Port**: `9003` (Xdebug 3.x default)
- **IDE Key**: `PHPSTORM`

### IDE Setup (PhpStorm/IntelliJ)

1. Go to **Settings** → **PHP** → **Debug**
2. Set **Xdebug port** to `9003`
3. Enable **Can accept external connections**
4. Go to **Settings** → **PHP** → **Servers**
5. Add a server:
   - **Name**: `symfony-local`
   - **Host**: `localhost`
   - **Port**: `8080`
   - **Debugger**: `Xdebug`
   - **Path mappings**: Map project root to `/var/www/html`

### Testing Xdebug

1. Enable Xdebug: `XDEBUG_MODE=debug make up`
2. Set a breakpoint in your PHP code
3. Start listening for debug connections in your IDE
4. Make a request to your application
5. The debugger should break at your breakpoint

### Disable Xdebug

To disable Xdebug (for better performance):
```bash
XDEBUG_MODE=off make up
```

Or simply don't set the `XDEBUG_MODE` variable (it's off by default).

