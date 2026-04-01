<?php

declare(strict_types=1);

use OpenSolid\Api\Command\GenerateOpenApiCommand;
use OpenSolid\Api\Controller\Decorator\ApiEarlyResponseDecorator;
use OpenSolid\Api\Controller\Decorator\ApiGetOrCreateResourceDecorator;
use OpenSolid\Api\Controller\Decorator\ApiPaginationDecorator;
use OpenSolid\Api\Controller\Decorator\ApiResponseDecorator;
use OpenSolid\Api\Controller\OpenApiController;
use OpenSolid\Api\OpenApi\OpenApiGenerator;
use OpenSolid\Api\OpenApi\OpenApiGeneratorFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return function (ContainerConfigurator $container): void {
    $services = $container->services();

    // OpenApi
    $services->set(OpenApiGeneratorFactory::class)
        ->args([
            service('logger'),
            service('type_info.resolver'),
            param('open_api.config'),
            tagged_iterator('open_api.path_parameter_schema_resolver'),
        ]);

    $services->set(OpenApiGenerator::class)
        ->factory([service(OpenApiGeneratorFactory::class), '__invoke']);

    // Command
    $services->set(GenerateOpenApiCommand::class)
        ->args([service(OpenApiGenerator::class)])
        ->tag('console.command');

    // Controller
    $services->set(OpenApiController::class)
        ->args([service(OpenApiGenerator::class)])
        ->tag('controller.service_arguments');

    // Controller Decorators
    $services->set(ApiEarlyResponseDecorator::class)
        ->args([service('type_info.resolver')])
        ->tag('callable_invoker.decorator', ['event' => 'kernel.controller', 'priority' => 100]);

    $services->set(ApiGetOrCreateResourceDecorator::class)
        ->tag('callable_invoker.decorator', ['event' => 'kernel.controller']);

    $services->set(ApiPaginationDecorator::class)
        ->tag('callable_invoker.decorator', ['event' => 'kernel.controller']);

    $services->set(ApiResponseDecorator::class)
        ->args([service('json_streamer.stream_writer')])
        ->tag('callable_invoker.decorator', ['event' => 'kernel.controller', 'priority' => -100]);
};
