# Configuration Reference

## Bundle Configuration

```yaml
# config/packages/open_solid_api.yaml
open_solid_api:

    # OpenAPI specification version
    version: '3.1.0' # default: OpenApi::DEFAULT_VERSION

    # Media type for request/response content
    media_type: 'application/json' # default

    # Directories to scan for controller and schema classes
    paths:
        - '%kernel.project_dir%/src/Controller'
        - '%kernel.project_dir%/src/Model'

    # Additional swagger-php Generator config
    config: {}

    # Enable OpenAPI spec validation
    validate: '%kernel.debug%' # default
```

### Options

#### `version`

The OpenAPI specification version to use (e.g. `3.0.0`, `3.1.0`). Passed to swagger-php's `Generator::setVersion()`.

#### `media_type`

The content type used for request bodies and response content. Defaults to `application/json`. This value is used by the `AugmentRequestBodies` and `AugmentOperations` processors when generating `MediaType` entries.

#### `paths`

An array of directories to scan for PHP classes. swagger-php analyses all files in these directories for OpenAPI attributes. Include both your controller directory (for route/operation discovery) and any directories containing schema classes.

#### `config`

An associative array passed to swagger-php's `Generator::setConfig()`. This can be used to configure swagger-php's built-in processors. See the [swagger-php documentation](https://zircote.github.io/swagger-php/guide/processor.html) for available options.

#### `validate`

When `true`, the generated OpenAPI spec is validated by swagger-php. Defaults to `%kernel.debug%`, so validation runs in development but not in production.

## Services

The bundle registers the following services:

| Service | Description |
|---------|-------------|
| `OpenApiGeneratorFactory` | Creates the configured `OpenApiGenerator` instance |
| `OpenApiGenerator` | Generates the OpenAPI spec (created via factory) |
| `GenerateOpenApiCommand` | The `openapi:generate` console command |
| `OpenApiController` | Serves the spec at `/docs.{format}` |
| `ApiEarlyResponseDecorator` | Resolves return types, handles early responses |
| `ApiGetOrCreateResourceDecorator` | Unwraps `GetOrCreateResource` |
| `ApiPaginationDecorator` | Wraps paginated results |
| `ApiResponseDecorator` | Streams JSON responses |

## Routes

The bundle provides one route:

| Name | Path | Methods | Description |
|------|------|---------|-------------|
| `openapi_docs` | `/docs.{format}` | GET | Serves the OpenAPI spec (`format`: `json` or `yaml`) |

Import it in your routing configuration with an optional prefix:

```yaml
# config/routes/open_solid_api.yaml
open_solid_api:
    resource: '@OpenSolidApiBundle/config/routes.php'
    prefix: /api  # optional
```

## Auto-Configuration

The bundle auto-configures services that implement `PathParameterSchemaResolver` with the `open_api.path_parameter_schema_resolver` tag. No manual tagging is needed if your service uses `autoconfigure: true`.
