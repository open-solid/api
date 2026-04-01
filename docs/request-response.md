# Request & Response Handling

## Request Bodies

The bundle automatically generates OpenAPI request body schemas from `#[MapRequestPayload]` parameters on your controller's `__invoke()` method.

```php
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[Post(path: '/products', name: 'api_create_product')]
final readonly class CreateProductController
{
    public function __invoke(
        #[MapRequestPayload] CreateProductPayload $payload,
    ): ProductView {
        // ...
    }
}
```

The `AugmentRequestBodies` processor detects the `#[MapRequestPayload]` attribute, resolves the parameter type, and generates:

```yaml
requestBody:
  required: true
  content:
    application/json:
      schema:
        $ref: '#/components/schemas/CreateProductPayload'
```

If the parameter is nullable (`?CreateProductPayload`), `required` is set to `false`.

### Schema Definition

Define your payload class with `#[OA\Schema]` and `#[OA\Property]` attributes:

```php
use OpenApi\Attributes as OA;

#[OA\Schema]
final readonly class CreateProductPayload
{
    public function __construct(
        #[OA\Property(description: 'The product name', minLength: 3, maxLength: 255)]
        public string $name,
        #[OA\Property(description: 'The price in cents', example: 100)]
        public int $price,
        #[OA\Property(description: 'Optional currency code', example: 'USD')]
        public string $currency = 'USD',
    ) {
    }
}
```

The `AugmentSchemas` processor infers `required` fields from PHP types: a property is required when it is not nullable and has no default value.

### Combining with Explicit Annotations

You can combine `#[MapRequestPayload]` with an explicit `#[OA\RequestBody]` annotation. The bundle fills in missing `content` and `required` from the parameter type while preserving your explicit metadata (e.g. description).

## Response Schemas

Response schemas are inferred from the controller's return type by the `AugmentOperations` processor.

### Object Return Type

```php
#[Get(path: '/products/{id}', name: 'api_find_product')]
final readonly class FindProductController
{
    public function __invoke(ProductId $id): ProductView
    {
        // ...
    }
}
```

Generates:

```yaml
responses:
  '200':
    content:
      application/json:
        schema:
          $ref: '#/components/schemas/ProductView'
```

### Void Return Type

```php
#[Delete(path: '/products/{id}', name: 'api_delete_product')]
final readonly class DeleteProductController
{
    public function __invoke(ProductId $id): void { }
}
```

Generates an empty `204` response (the default status code for `#[Delete]`).

### GetOrCreate Pattern

For upsert endpoints that may either retrieve an existing resource or create a new one, return `GetOrCreateResource<T>`:

```php
use OpenSolid\Core\Domain\Model\GetOrCreateResource;

#[Put(path: '/products/{id}', name: 'api_replace_product')]
final readonly class ReplaceProductController
{
    /**
     * @return GetOrCreateResource<ProductView>
     */
    public function __invoke(
        ProductId $id,
        #[MapRequestPayload] ReplaceProductPayload $payload,
    ): GetOrCreateResource {
        // Return GetOrCreateResource::created($view) or GetOrCreateResource::existing($view)
    }
}
```

This generates dual responses in the spec:

```yaml
responses:
  '200':
    content:
      application/json:
        schema:
          $ref: '#/components/schemas/ProductView'
  '201':
    content:
      application/json:
        schema:
          $ref: '#/components/schemas/ProductView'
```

At runtime, the `ApiGetOrCreateResourceDecorator` unwraps the `GetOrCreateResource` and sets the appropriate status code (200 or 201).

### Symfony Response Return Types

If the controller returns a `JsonResponse` or any other Symfony `Response` subclass, the bundle skips response inference and lets Symfony handle it normally.

## Query Parameters

Use `#[MapQueryString]` with a DTO class to define query parameters. Each property annotated with `#[OA\QueryParameter]` becomes an individual query parameter in the spec:

```php
use OpenApi\Attributes as OA;
use OpenSolid\Api\Controller\Model\Paginator\PaginationParams;

final class FindProductsQuery
{
    use PaginationParams; // Adds page and itemsPerPage parameters

    #[OA\QueryParameter(description: 'Filter by product name')]
    public ?string $name = null;
}
```

```php
#[GetCollection(path: '/products', name: 'api_find_products')]
final readonly class FindProductsController
{
    public function __invoke(#[MapQueryString] ?FindProductsQuery $query = null): Paginator
    {
        // ...
    }
}
```

The `AugmentQueryParameterSets` processor expands the DTO into individual parameters, and `AugmentQueryParameters` infers their types, defaults, and required flags from PHP reflection.

## Controller Decorator Pipeline

At runtime, API responses pass through a chain of controller decorators that handle serialization and status codes. The decorators only activate for routes with the `_api_controller` default (set automatically by the routing attributes).

**Execution order:**

| Priority | Decorator                        | Purpose                                          |
|----------|----------------------------------|--------------------------------------------------|
| 100      | `ApiEarlyResponseDecorator`      | Resolves return type; short-circuits for `JsonResponse` or `null` |
| 0        | `ApiGetOrCreateResourceDecorator`| Unwraps `GetOrCreateResource` and sets status code |
| 0        | `ApiPaginationDecorator`         | Wraps `Paginator` results in `PageResponse`       |
| -100     | `ApiResponseDecorator`           | Streams the response as JSON via `JsonStreamWriter` |

The decorators communicate through `CallableMetadata` attributes and `ResponseOptions` objects, passing resolved type information and status codes down the chain.
