<?php

declare(strict_types=1);

namespace OpenSolid\Api\Routing\Attribute;

use Symfony\Component\Routing\Attribute\DeprecatedAlias;
use Symfony\Component\Routing\Attribute\Route;

abstract class ApiRoute extends Route
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
        public ?string $description = null,
        public ?string $summary = null,
        public array $tags = [],
        public bool $deprecated = false,
    ) {
        if (null === $statusCode && $this instanceof Post) {
            $statusCode = 201;
        }

        parent::__construct(
            path: $path,
            name: $name,
            requirements: $requirements,
            options: $options,
            defaults: $defaults + array_filter([
                '_api_controller' => true,
                '_api_status_code' => $statusCode,
            ], fn ($v) => null !== $v),
            host: $host,
            methods: [static::getMethod()],
            schemes: $schemes,
            condition: $condition,
            priority: $priority,
            format: $format,
            stateless: true,
            env: $env,
            alias: $alias,
        );
    }

    abstract public static function getMethod(): string;
}
