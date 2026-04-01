<?php

declare(strict_types=1);

namespace OpenSolid\Api\Controller\Decorator;

use OpenSolid\Api\Controller\Model\ResponseOptions;
use OpenSolid\CallableInvoker\CallableMetadata;
use OpenSolid\CallableInvoker\Decorator\CallableClosure;
use OpenSolid\Core\Domain\Model\GetOrCreateResource;
use Symfony\Component\TypeInfo\Type\GenericType;

final readonly class ApiGetOrCreateResourceDecorator extends AbstractApiDecorator
{
    public function decorate(CallableClosure $callable, CallableMetadata $metadata): mixed
    {
        $response = $callable->call();

        if (!$response instanceof GetOrCreateResource) {
            return $response;
        }

        /** @var GenericType $type */
        $type = $metadata->getAttribute('return_type');

        return new ResponseOptions(
            response: $response->resource,
            type: $type->getVariableTypes()[0],
            statusCode: $response->created ? 201 : null,
        );
    }
}
