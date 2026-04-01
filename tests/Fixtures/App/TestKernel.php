<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App;

use OpenSolid\Api\OpenApi\OpenApiGenerator;
use OpenSolid\Api\OpenSolidApiBundle;
use OpenSolid\Api\Tests\Fixtures\App\Model\ProductIdPathParameterSchemaResolver;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new class implements CompilerPassInterface {
            public function process(ContainerBuilder $container): void
            {
                if ($container->hasDefinition('open_solid_api.generator')) {
                    $container->getDefinition('open_solid_api.generator')->setPublic(true);
                }

                if ($container->hasDefinition('open_solid_api.generator_factory')) {
                    $container->getDefinition('open_solid_api.generator_factory')
                        ->replaceArgument(0, new Definition(NullLogger::class));
                }
            }
        });
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new OpenSolidApiBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->loadFromExtension('framework', [
                'test' => true,
                'secret' => 'test',
                'http_method_override' => false,
                'type_info' => ['enabled' => true],
                'json_streamer' => ['enabled' => true],
            ]);

            $container->loadFromExtension('open_solid_api', [
                'version' => '3.1.0',
                'paths' => [
                    __DIR__,
                ],
                'validate' => false,
            ]);

            $container->register(ProductIdPathParameterSchemaResolver::class)
                ->setAutoconfigured(true);
        });
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/open_solid_api_tests/cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/open_solid_api_tests/log';
    }
}
