# Routing Attributes

The bundle provides HTTP method attributes that extend Symfony's `#[Route]` with OpenAPI metadata. Place them on invokable controller classes to define both the Symfony route and the OpenAPI operation in one declaration.

## Available Attributes

| Attribute        | HTTP Method | Default Status Code |
|------------------|-------------|---------------------|
| `#[Get]`         | GET         | 200                 |
| `#[Post]`        | POST        | 201                 |
| `#[Put]`         | PUT         | 200                 |
| `#[Patch]`       | PATCH       | 200                 |
| `#[Delete]`      | DELETE      | 204                 |
| `#[Head]`        | HEAD        | 200                 |
| `#[Options]`     | OPTIONS     | 200                 |
| `#[GetCollection]` | GET       | 200                 |

All attributes live in the `OpenSolid\Api\Routing\Attribute` namespace.

## Parameters

Every attribute accepts all standard Symfony `#[Route]` parameters plus the following OpenAPI-specific ones:

| Parameter     | Type     | Description                           |
|---------------|----------|---------------------------------------|
| `description` | `?string` | Operation description                |
| `summary`     | `?string` | Short operation summary              |
| `tags`        | `array`  | Tags for grouping operations          |
| `deprecated`  | `bool`   | Mark the operation as deprecated      |
| `statusCode`  | `?int`   | Override the default status code      |

## Usage

```php
use OpenSolid\Api\Routing\Attribute\Get;
use OpenSolid\Api\Routing\Attribute\Post;
use OpenSolid\Api\Routing\Attribute\Delete;

#[Get(
    path: '/products/{id}',
    name: 'api_find_product',
    description: 'Find a Product',
    summary: 'Retrieves a single product by its unique identifier.',
    tags: ['Product'],
)]
final readonly class FindProductController
{
    public function __invoke(ProductId $id): ProductView { /* ... */ }
}

#[Post(
    path: '/products',
    name: 'api_create_product',
    description: 'Create a Product',
    tags: ['Product'],
)]
final readonly class CreateProductController
{
    public function __invoke(#[MapRequestPayload] CreateProductPayload $payload): ProductView { /* ... */ }
}

#[Delete(
    path: '/products/{id}',
    name: 'api_delete_product',
    description: 'Delete a Product',
    tags: ['Product'],
)]
final readonly class DeleteProductController
{
    public function __invoke(ProductId $id): void { }
}
```

## Route Defaults

Each attribute automatically sets the following route defaults:

- `_api_controller: true` — Identifies the route as an API endpoint (used by controller decorators)
- `_api_status_code` — The inferred status code for the response

## GetCollection

`#[GetCollection]` is a specialized `GET` attribute for paginated collection endpoints. It adds an `_api_pagination` route default:

```php
use OpenSolid\Api\Routing\Attribute\GetCollection;

#[GetCollection(
    path: '/products',
    name: 'api_find_products',
    description: 'Find Products',
    tags: ['Product'],
    pagination: true, // default
)]
final readonly class FindProductsController
{
    /**
     * @return Paginator<ProductView>
     */
    public function __invoke(#[MapQueryString] ?FindProductsQuery $query = null): Paginator
    {
        // ...
    }
}
```

When `pagination` is `true` (the default), the response schema is automatically wrapped in the [paginated response format](pagination.md).

## How It Works

Under the hood, the `GenerateOperationsFromApiRoutes` processor scans all classes in the configured `paths` for `ApiRoute` attributes and generates the corresponding OpenAPI operations (`OA\Get`, `OA\Post`, etc.), eliminating the need for duplicate swagger-php annotations.
