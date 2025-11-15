# OpenAPI Documentation

This folder contains all OpenAPI-related files for the Order Management API.

## Files

- `generate.php` - Script to generate OpenAPI documentation from PHP attributes
- `openapi.json` - Generated OpenAPI 3.0 specification file
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

The generation script will automatically pick up these attributes and include them in the OpenAPI specification.
