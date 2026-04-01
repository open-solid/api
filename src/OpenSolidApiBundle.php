<?php

declare(strict_types=1);

namespace OpenSolid\Api;

use OpenSolid\Api\OpenApi\Resolver\PathParameterSchemaResolver;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class OpenSolidApiBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->parameters()
            ->set('open_api.config', $config)
        ;

        $builder->registerForAutoconfiguration(PathParameterSchemaResolver::class)
            ->addTag('open_api.path_parameter_schema_resolver');

        $container->import('../config/services.php');
    }

    public function getAlias(): string
    {
        return 'open_solid_api';
    }
}
