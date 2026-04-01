# Getting Started

## Installation

```console
composer require open-solid/api
```

If you're using Symfony Flex, the bundle is registered automatically. Otherwise, add it to your `config/bundles.php`:

```php
return [
    // ...
    OpenSolid\Api\OpenSolidApiBundle::class => ['all' => true],
];
```

## Minimal Configuration

```yaml
# config/packages/open_solid_api.yaml
open_solid_api:
    paths:
        - '%kernel.project_dir%/src/Controller'
```

The `paths` option tells the bundle which directories to scan for controller classes.

## Define Your First Endpoint

Create a controller class with a routing attribute:

```php
namespace App\Controller;

use OpenSolid\Api\Routing\Attribute\Get;

#[Get(
    path: '/hello',
    name: 'api_hello',
    description: 'Say hello',
    tags: ['Greeting'],
)]
final readonly class HelloController
{
    public function __invoke(): array
    {
        return ['message' => 'Hello, world!'];
    }
}
```

The bundle will generate an OpenAPI `GET /hello` operation from this class automatically.

## View the Spec

The bundle registers a route at `/docs.{format}`. Access your spec at:

- **JSON**: `http://localhost:8000/docs.json`
- **YAML**: `http://localhost:8000/docs.yaml`

Or export it to a file:

```console
php bin/console openapi:generate openapi.json
php bin/console openapi:generate openapi.yaml
```

## Adding OpenAPI Metadata

Use swagger-php attributes to define top-level metadata such as the API title, version, servers, and tags. Place them on any class within the scanned paths:

```php
namespace App\Controller;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0',
    description: 'My API description',
    title: 'My API',
)]
#[OA\Server(url: 'https://api.example.com')]
#[OA\Tag(name: 'Product', description: 'Product management')]
class OpenApiSpec
{
}
```

## Next Steps

- [Routing Attributes](routing-attributes.md) — Learn about all available HTTP method attributes
- [Request & Response Handling](request-response.md) — Request bodies, response schemas, and the decorator pipeline
- [Pagination](pagination.md) — Built-in paginated collection support
- [Configuration Reference](configuration.md) — All configuration options
