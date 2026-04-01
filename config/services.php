<?php

declare(strict_types=1);

use OpenSolid\Api\Command\GenerateOpenApiCommand;
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
    $services->set('open_solid_api.generator_factory', OpenApiGeneratorFactory::class)
        ->args([
            service('logger'),
            service('type_info.resolver'),
            param('open_api.config'),
            tagged_iterator('open_api.path_parameter_schema_resolver'),
        ]);
    $services->alias(OpenApiGeneratorFactory::class, 'open_solid_api.generator_factory');

    $services->set('open_solid_api.generator', OpenApiGenerator::class)
        ->factory([service('open_solid_api.generator_factory'), '__invoke']);
    $services->alias(OpenApiGenerator::class, 'open_solid_api.generator');

    // Command
    $services->set('open_solid_api.command.generate', GenerateOpenApiCommand::class)
        ->args([service('open_solid_api.generator')])
        ->tag('console.command');
    $services->alias(GenerateOpenApiCommand::class, 'open_solid_api.command.generate');

    // Controller
    $services->set('open_solid_api.controller.docs', OpenApiController::class)
        ->args([service('open_solid_api.generator')])
        ->tag('controller.service_arguments');
    $services->alias(OpenApiController::class, 'open_solid_api.controller.docs');
};
