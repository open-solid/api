<?php

declare(strict_types=1);

namespace OpenSolid\Api\OpenApi;

use OpenApi\Annotations\OpenApi;
use OpenApi\Generator;

final readonly class OpenApiGenerator
{
    public function __construct(
        private Generator $generator,
        private array $config,
    ) {
    }

    public function generate(): OpenApi
    {
        return $this->generator->generate(
            sources: $this->config['paths'],
            validate: $this->config['validate'],
        );
    }
}
