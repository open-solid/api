<?php

declare(strict_types=1);

namespace OpenSolid\Api\Routing\Attribute;

use Symfony\Component\Routing\Attribute\DeprecatedAlias;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class GetCollection extends ApiRoute
{
    public function __construct(
        array|string $path,
        ?string $name = null,
        array $requirements = [],
        array $options = [],
        array $defaults = [],
        ?string $host = null,
        array|string $schemes = [],
        ?string $condition = null,
        ?int $priority = null,
        ?string $format = null,
        string|array|null $env = null,
        string|DeprecatedAlias|array $alias = [],
        ?int $statusCode = null,
        ?string $description = null,
        ?string $summary = null,
        array $tags = [],
        bool $deprecated = false,
        bool $pagination = true,
    ) {
        parent::__construct(
            path: $path,
            name: $name,
            requirements: $requirements,
            options: $options,
            defaults: $defaults + ['_api_pagination' => $pagination],
            host: $host,
            schemes: $schemes,
            condition: $condition,
            priority: $priority,
            format: $format,
            env: $env,
            alias: $alias,
            statusCode: $statusCode,
            description: $description,
            summary: $summary,
            tags: $tags,
            deprecated: $deprecated,
        );
    }

    public static function getMethod(): string
    {
        return 'GET';
    }
}
