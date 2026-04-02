<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Controller\Decorator;

use OpenSolid\Api\Controller\Decorator\AbstractApiDecorator;
use OpenSolid\CallableInvoker\CallableMetadata;
use OpenSolid\CallableInvoker\Decorator\CallableClosure;

final readonly class ConcreteApiDecorator extends AbstractApiDecorator
{
    public function decorate(CallableClosure $callable, CallableMetadata $metadata): mixed
    {
        return $callable->call();
    }
}
