<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App\Model;

use OpenApi\Attributes\PathParameter;
use OpenSolid\Api\OpenApi\Resolver\PathParameterSchemaResolver;

final class ProductIdPathParameterSchemaResolver implements PathParameterSchemaResolver
{
    public function resolve(\ReflectionParameter $reflection, PathParameter $parameter): bool
    {
        $type = $reflection->getType();

        if (!$type instanceof \ReflectionNamedType || ProductId::class !== $type->getName()) {
            return false;
        }

        $parameter->description = 'The product ID';
        $parameter->schema->type = 'string';
        $parameter->schema->format = 'uuid';
        $parameter->example = '019d0121-5df2-77df-be75-8933613d53ab';

        return true;
    }
}
