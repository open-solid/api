# Path Parameter Resolvers

When your controllers use value objects as path parameters (e.g. `ProductId` instead of `string`), the bundle needs to know how to describe them in the OpenAPI spec. Path parameter resolvers handle this.

## The PathParameterSchemaResolver Interface

```php
namespace OpenSolid\Api\OpenApi\Resolver;

use OpenApi\Attributes\PathParameter;

interface PathParameterSchemaResolver
{
    /**
     * Resolves schema and metadata for a path parameter from its PHP type.
     *
     * Returns true to stop the chain, false to let the next resolver try.
     */
    public function resolve(\ReflectionParameter $reflection, PathParameter $parameter): bool;
}
```

## Creating a Resolver

Implement the interface and modify the `PathParameter` in place:

```php
namespace App\OpenApi\Resolver;

use App\Model\ProductId;
use OpenApi\Attributes\PathParameter;
use OpenSolid\Api\OpenApi\Resolver\PathParameterSchemaResolver;

final class ProductIdSchemaResolver implements PathParameterSchemaResolver
{
    public function resolve(\ReflectionParameter $reflection, PathParameter $parameter): bool
    {
        $type = $reflection->getType();

        if (!$type instanceof \ReflectionNamedType || ProductId::class !== $type->getName()) {
            return false; // Not our type — pass to next resolver
        }

        $parameter->description = 'The product ID';
        $parameter->schema->type = 'string';
        $parameter->schema->format = 'uuid';
        $parameter->example = '019d0121-5df2-77df-be75-8933613d53ab';

        return true; // Handled — stop the chain
    }
}
```

## Registration

Implementations of `PathParameterSchemaResolver` are auto-configured by the bundle. Just register the class as a service:

```yaml
# config/services.yaml
services:
    App\OpenApi\Resolver\ProductIdSchemaResolver: ~
```

Or if you use `autoconfigure: true` (the default in Symfony), no configuration is needed at all — the bundle tags the service automatically.

## Using Path Parameters in Controllers

Annotate value object parameters with `#[PathParameter]` from swagger-php:

```php
use OpenApi\Attributes\PathParameter;

#[Get(path: '/products/{id}', name: 'api_find_product')]
final readonly class FindProductController
{
    public function __invoke(#[PathParameter] ProductId $id): ProductView
    {
        // ...
    }
}
```

The `MergeMethodAnnotationsIntoOperations` processor links the parameter to the operation, and `AugmentQueryParameters` delegates to your resolver chain to fill in the schema.

## Chain of Responsibility

Multiple resolvers can be registered. They are called in order until one returns `true`:

```
AugmentQueryParameters
  → ProductIdSchemaResolver    → returns false (not ProductId)
  → OrderIdSchemaResolver      → returns true  (handled!)
  → (chain stops)
```

This lets you define resolvers for each value object type independently.
