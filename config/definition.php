<?php

declare(strict_types=1);

use OpenApi\Annotations as OA;
use OpenSolid\CallableInvoker\Decorator\CallableDecoratorInterface;
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
            ->booleanNode('decorators')
                ->defaultValue(interface_exists(CallableDecoratorInterface::class))
                ->info('Enable API controller decorators (requires open-solid/callable-invoker)')
            ->end()
            ->arrayNode('cache_headers')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('vary')
                        ->scalarPrototype()->end()
                        ->defaultValue(['Content-Type', 'Authorization', 'Origin'])
                    ->end()
                ->end()
            ->end()
        ->end();
};
