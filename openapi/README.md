# OpenAPI Documentation

This folder contains all OpenAPI-related files for the Order Management API.

## Files

- `generate.php` - Script to generate OpenAPI documentation from PHP attributes
- `openapi.yaml` - Base OpenAPI configuration (info, servers) in YAML format
- `openapi.json` - Generated OpenAPI 3.0 specification file (merged from YAML + PHP attributes)
- `README.md` - This file

## Generation

The OpenAPI documentation is automatically generated before each commit via the git pre-commit hook.

To manually generate the documentation:

```bash
docker-compose exec php php openapi/generate.php
```

## Access

- **Swagger UI (Interactive)**: http://localhost:8080/swagger.html
- **OpenAPI JSON**: http://localhost:8080/openapi.json
- **Nelmio Bundle UI**: http://localhost:8080/api/doc (if bundle is configured)

## Usage

1. Open http://localhost:8080/swagger.html in your browser
2. You'll see the interactive Swagger UI with all API endpoints
3. Click "Try it out" on any endpoint to test it directly
4. Fill in the request parameters and click "Execute" to make real API calls

## Configuration

The base OpenAPI configuration (info, servers) is defined in `openapi.yaml`. This file contains:
- API title, description, and version
- Server URLs and descriptions

The generation script merges this base configuration with PHP attributes from controllers to create the final OpenAPI specification.

## Adding Documentation

Document your endpoints using OpenAPI attributes in your controllers:

```php
use OpenApi\Attributes as OA;

#[Route('/api/orders', methods: ['POST'])]
#[OA\Post(
    path: '/api/orders',
    summary: 'Create a new order',
    // ... more attributes
)]
public function createOrder(Request $request): JsonResponse
{
    // ...
}
```

The generation script will automatically:
1. Load base configuration from `openapi.yaml`
2. Scan PHP attributes from controllers
3. Merge them together to create the final OpenAPI specification
