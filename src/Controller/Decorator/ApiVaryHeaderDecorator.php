<?php

declare(strict_types=1);

namespace OpenSolid\Api\Controller\Decorator;

use OpenSolid\CallableInvoker\CallableMetadata;
use OpenSolid\CallableInvoker\Decorator\CallableClosure;
use Symfony\Component\HttpFoundation\Response;

final readonly class ApiVaryHeaderDecorator extends AbstractApiDecorator
{
    /**
     * @param list<string> $headers
     */
    public function __construct(
        private array $headers,
    ) {
    }

    public function decorate(CallableClosure $callable, CallableMetadata $metadata): mixed
    {
        $response = $callable->call();

        if ($this->headers && $response instanceof Response) {
            $response->setVary($this->headers, false);
        }

        return $response;
    }
}
