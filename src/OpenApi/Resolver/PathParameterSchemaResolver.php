<?php

declare(strict_types=1);

namespace OpenSolid\Api\OpenApi\Resolver;

use OpenApi\Attributes\PathParameter;

interface PathParameterSchemaResolver
{
    /**
     * Resolves schema and parameter metadata for a path parameter from its PHP type.
     *
     * Returns true to short-circuit the chain, false to continue to the next resolver.
     */
    public function resolve(\ReflectionParameter $reflection, PathParameter $parameter): bool;
}
