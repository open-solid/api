<?php

declare(strict_types=1);

use OpenApi\Annotations as OA;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return function (DefinitionConfigurator $definition): void {
    $definition->rootNode()
        ->children()
            ->scalarNode('version')
                ->defaultValue(OA\OpenApi::DEFAULT_VERSION)
            ->end()
            ->scalarNode('media_type')
                ->defaultValue('application/json')
            ->end()
            ->variableNode('config')
                ->defaultValue([])
            ->end()
            ->variableNode('paths')
                ->defaultValue([])
            ->end()
            ->booleanNode('validate')
                ->defaultValue('%kernel.debug%')
            ->end()
        ->end();
};
