<?php

declare(strict_types=1);

namespace OpenSolid\Api\Controller\Decorator;

use OpenSolid\Api\Controller\Model\ResponseOptions;
use OpenSolid\Api\Routing\Attribute\Delete;
use OpenSolid\Api\Routing\Attribute\Post;
use OpenSolid\CallableInvoker\CallableMetadata;
use OpenSolid\CallableInvoker\Decorator\CallableClosure;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\JsonStreamer\JsonStreamWriter;
use Symfony\Component\TypeInfo\Type;

final readonly class ApiResponseDecorator extends AbstractApiDecorator
{
    public function __construct(
        private ?JsonStreamWriter $jsonStreamWriter = null,
    ) {
    }

    public function decorate(CallableClosure $callable, CallableMetadata $metadata): JsonResponse|StreamedResponse
    {
        $response = $callable->call();

        if ($response instanceof JsonResponse) {
            return $response;
        }

        if ($response instanceof ResponseOptions) {
            $type = $response->type;
            $statusCode = $response->statusCode;
            $headers = $response->headers;
            $response = $response->response;
        }

        /** @var Request $event */
        $request = $metadata->context['request'];
        /** @var Type $returnType */
        $type ??= $metadata->getAttribute('return_type');
        $statusCode ??= $request->attributes->getInt('_api_status_code', match ($request->attributes->getString('_api_route_class')) {
            Post::class => 201,
            Delete::class => 204,
            default => 200,
        });

        return new StreamedResponse(
            callbackOrChunks: $this->jsonStreamWriter->write($response, $type),
            status: $statusCode,
            headers: $headers ?? ['Content-Type' => 'application/json'],
        );
    }
}
