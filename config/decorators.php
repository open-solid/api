<?php

declare(strict_types=1);

use OpenSolid\Api\Controller\Decorator\ApiEarlyResponseDecorator;
use OpenSolid\Api\Controller\Decorator\ApiGetOrCreateResourceDecorator;
use OpenSolid\Api\Controller\Decorator\ApiPaginationDecorator;
use OpenSolid\Api\Controller\Decorator\ApiResponseDecorator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(ApiEarlyResponseDecorator::class)
        ->args([service('type_info.resolver')])
        ->tag('callable_invoker.decorator', ['event' => 'kernel.controller', 'priority' => 100]);

    $services->set(ApiGetOrCreateResourceDecorator::class)
        ->tag('callable_invoker.decorator', ['event' => 'kernel.controller']);

    $services->set(ApiPaginationDecorator::class)
        ->tag('callable_invoker.decorator', ['event' => 'kernel.controller']);

    $services->set(ApiResponseDecorator::class)
        ->args([
            service('json_streamer.stream_writer')->nullOnInvalid(),
        ])
        ->tag('callable_invoker.decorator', ['event' => 'kernel.controller', 'priority' => -100]);
};
