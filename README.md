# OpenSolid API Bundle

A Symfony bundle that automatically generates [OpenAPI](https://www.openapis.org/) specifications from your controller classes using [swagger-php](https://github.com/zircote/swagger-php).

Define your API endpoints with PHP attributes, and the bundle takes care of generating a complete OpenAPI spec — including operations, request bodies, query parameters, path parameters, response schemas, and pagination.

## Features

- **Route-driven spec generation** — OpenAPI operations are derived from custom routing attributes (`#[Get]`, `#[Post]`, etc.), eliminating duplicate annotations
- **Automatic request body inference** — Detects `#[MapRequestPayload]` parameters and generates request body schemas
- **Query parameter expansion** — Expands `#[MapQueryString]` DTOs into individual query parameters
- **Response type inference** — Infers response schemas from controller return types
- **Pagination support** — Built-in `Paginator` interface with standardized paginated response format
- **GetOrCreate pattern** — Dual 200/201 responses for upsert endpoints
- **Custom path parameter resolvers** — Extensible resolution of value object path parameters (e.g. `ProductId` to UUID)
- **JSON streaming** — Responses are streamed via Symfony's JsonStreamer for memory efficiency
- **Serve & export** — Serve the spec at `/docs.json` or export it with `php bin/console openapi:generate`

## Installation

```console
composer require open-solid/api
```

## Quick Start

```php
#[Get(
    path: '/products/{id}',
    name: 'api_find_product',
    description: 'Find a Product',
    summary: 'Retrieves a single product by its unique identifier.',
    tags: ['Product'],
)]
final readonly class FindProductController
{
    public function __invoke(#[PathParameter] ProductId $id): ProductView
    {
        // ...
    }
}
```

This single class generates a complete OpenAPI operation with path parameters, response schema, and status codes — no extra annotations needed.

## Documentation

- [Getting Started](docs/getting-started.md)
- [Routing Attributes](docs/routing-attributes.md)
- [Request & Response Handling](docs/request-response.md)
- [Pagination](docs/pagination.md)
- [OpenAPI Generation](docs/openapi-generation.md)
- [Path Parameter Resolvers](docs/path-parameter-resolvers.md)
- [Configuration Reference](docs/configuration.md)

## License

MIT
