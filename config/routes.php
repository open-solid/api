<?php

declare(strict_types=1);

use OpenSolid\Api\Controller\OpenApiController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add('openapi_docs', '/docs.{format}')
        ->controller(OpenApiController::class)
        ->requirements(['format' => 'json|yaml'])
        ->methods(['GET']);
};
