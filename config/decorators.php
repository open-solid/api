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

    $services->set('open_solid_api.decorator.early_response', ApiEarlyResponseDecorator::class)
        ->args([service('type_info.resolver')])
        ->tag('callable_invoker.decorator', ['groups' => ['kernel.controller'], 'priority' => 100]);
    $services->alias(ApiEarlyResponseDecorator::class, 'open_solid_api.decorator.early_response');

    $services->set('open_solid_api.decorator.get_or_create_resource', ApiGetOrCreateResourceDecorator::class)
        ->tag('callable_invoker.decorator', ['groups' => ['kernel.controller']]);
    $services->alias(ApiGetOrCreateResourceDecorator::class, 'open_solid_api.decorator.get_or_create_resource');

    $services->set('open_solid_api.decorator.pagination', ApiPaginationDecorator::class)
        ->tag('callable_invoker.decorator', ['groups' => ['kernel.controller']]);
    $services->alias(ApiPaginationDecorator::class, 'open_solid_api.decorator.pagination');

    $services->set('open_solid_api.decorator.response', ApiResponseDecorator::class)
        ->args([
            service('json_streamer.stream_writer')->nullOnInvalid(),
        ])
        ->tag('callable_invoker.decorator', ['groups' => ['kernel.controller'], 'priority' => -100]);
    $services->alias(ApiResponseDecorator::class, 'open_solid_api.decorator.response');
};
