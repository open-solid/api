<?php

declare(strict_types=1);

namespace OpenSolid\Api\Controller\Decorator;

use OpenSolid\CallableInvoker\CallableMetadata;
use OpenSolid\CallableInvoker\Decorator\CallableDecoratorInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;

abstract readonly class AbstractApiDecorator implements CallableDecoratorInterface
{
    public function supports(CallableMetadata $metadata): bool
    {
        /** @var ControllerArgumentsEvent $event */
        $event = $metadata->context['event'];
        $request = $event->getRequest();

        return $event->isMainRequest() && 'json' === $request->getPreferredFormat() && $request->attributes->has('_api_controller');
    }
}
