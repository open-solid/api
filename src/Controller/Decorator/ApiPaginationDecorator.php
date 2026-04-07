<?php

declare(strict_types=1);

namespace OpenSolid\Api\Controller\Decorator;

use OpenSolid\Api\Controller\Model\Paginator\PageResponse;
use OpenSolid\Api\Controller\Model\ResponseOptions;
use OpenSolid\CallableInvoker\CallableMetadata;
use OpenSolid\CallableInvoker\Decorator\CallableClosure;
use OpenSolid\Core\Domain\Repository\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\TypeInfo\Type;

final readonly class ApiPaginationDecorator extends AbstractApiDecorator
{
    public function decorate(CallableClosure $callable, CallableMetadata $metadata): mixed
    {
        $response = $callable->call();

        /** @var Request $event */
        $request = $metadata->context['request'];

        if (!$response instanceof Paginator || !$request->attributes->getBoolean('_api_pagination')) {
            return $response;
        }

        $response = new PageResponse(
            items: $response,
            totalItems: $response->getTotalItems(),
        );

        return new ResponseOptions($response, $this->resolveResponseType($metadata));
    }

    private function resolveResponseType(CallableMetadata $metadata): Type
    {
        /** @var Type\CollectionType $returnType */
        $returnType = $metadata->getAttribute('return_type');
        $variableTypes = $returnType->getWrappedType()->getVariableTypes();

        return Type::generic(Type::object(PageResponse::class), end($variableTypes));
    }
}
