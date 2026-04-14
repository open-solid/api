<?php

declare(strict_types=1);

namespace OpenSolid\Api\Controller\Decorator;

use OpenSolid\CallableInvoker\CallableMetadata;
use OpenSolid\CallableInvoker\Decorator\CallableClosure;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\TypeInfo\TypeResolver\TypeResolverInterface;

final readonly class ApiEarlyResponseDecorator extends AbstractApiDecorator
{
    public function __construct(
        private TypeResolverInterface $typeResolver,
    ) {
    }

    public function decorate(CallableClosure $callable, CallableMetadata $metadata): mixed
    {
        $response = $callable->call();

        if ($response instanceof JsonResponse) {
            return $response;
        }

        if (null === $response) {
            $request = $metadata->context['request'];
            $statusCode = $request->attributes->getInt('_api_status_code') ?: 204;

            return new JsonResponse(null, $statusCode);
        }

        $metadata->setAttribute('return_type', $this->typeResolver->resolve($metadata->function));

        return $response;
    }
}
