# OpenAPI Generation

## How the Spec Is Generated

The bundle builds on [swagger-php](https://github.com/zircote/swagger-php) and extends its processor pipeline with custom processors that understand Symfony conventions.

### Processor Pipeline

The `OpenApiGeneratorFactory` assembles the following pipeline (custom processors are marked with **\***):

```
DocBlockDescriptions
MergeIntoOpenApi
MergeIntoComponents
ExpandClasses / ExpandInterfaces / ExpandTraits / ExpandEnums
AugmentSchemas (swagger-php)
AugmentRequestBody (swagger-php)
  └─ * AugmentSchemas             ← Infers required fields from PHP types
AugmentProperties
AugmentDiscriminators
  * GenerateOperationsFromApiRoutes ← Creates operations from routing attributes
  * MergeMethodAnnotationsIntoOperations ← Merges __invoke() params into operations
  * AugmentOperations               ← Infers response schemas from return types
  * AugmentRequestBodies             ← Infers request bodies from #[MapRequestPayload]
BuildPaths
  * AugmentQueryParameterSets        ← Expands #[MapQueryString] DTOs
AugmentParameters (swagger-php)
AugmentRefs
  * AugmentQueryParameters           ← Fills in parameter types, defaults, required
CleanUnmerged / ...
```

### What Each Custom Processor Does

| Processor | Purpose |
|-----------|---------|
| `GenerateOperationsFromApiRoutes` | Scans classes for `#[Get]`, `#[Post]`, etc. and creates `OA\Operation` annotations |
| `MergeMethodAnnotationsIntoOperations` | Links `#[PathParameter]` and `#[OA\RequestBody]` from `__invoke()` params to their parent operation |
| `AugmentOperations` | Infers response schemas from controller return types, handles `Paginator<T>` and `GetOrCreateResource<T>` |
| `AugmentRequestBodies` | Creates request body definitions from `#[MapRequestPayload]` parameters |
| `AugmentQueryParameterSets` | Expands `#[MapQueryString]` DTO classes into individual `#[OA\QueryParameter]` entries |
| `AugmentQueryParameters` | Fills schema type, format, default, and required for parameters backed by PHP reflection |
| `AugmentSchemas` | Infers `required` fields on schemas from property nullability and default values |

## Serving the Spec

The bundle registers a route that serves the generated spec over HTTP:

| URL | Format |
|-----|--------|
| `/docs.json` | JSON |
| `/docs.yaml` | YAML |

The route is handled by `OpenApiController` and can be imported in your routing configuration:

```yaml
# config/routes/open_solid_api.yaml
open_solid_api:
    resource: '@OpenSolidApiBundle/config/routes.php'
```

## Exporting the Spec

Use the `openapi:generate` console command to write the spec to a file:

```console
# JSON (default)
php bin/console openapi:generate

# Custom output path
php bin/console openapi:generate openapi.json

# YAML format
php bin/console openapi:generate openapi.yaml
```

The format is determined by the file extension (`.json`, `.yaml`, or `.yml`).

## Combining with swagger-php Annotations

The bundle's processors work alongside swagger-php's built-in annotation system. You can use standard `#[OA\*]` attributes to:

- Define schemas on DTO classes (`#[OA\Schema]`, `#[OA\Property]`)
- Add path parameters (`#[OA\PathParameter]`)
- Override inferred operations with explicit `#[OA\Get]`, `#[OA\Post]`, etc.
- Set API metadata (`#[OA\Info]`, `#[OA\Server]`, `#[OA\Tag]`)

The bundle never overrides explicitly defined annotations — it only fills in what's missing.
