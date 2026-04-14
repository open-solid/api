<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\Functional;

use OpenSolid\Api\OpenSolidApiBundle;
use OpenSolid\CallableInvoker\CallableInvokerBundle;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class FunctionalTestKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new CallableInvokerBundle(),
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
                'router' => [
                    'utf8' => true,
                    'resource' => __DIR__.'/Controller/',
                    'type' => 'attribute',
                ],
            ]);

            $container->loadFromExtension('open_solid_api', [
                'version' => '3.1.0',
                'paths' => [__DIR__],
                'validate' => false,
            ]);

            $container->register('logger', NullLogger::class);

            foreach (glob(__DIR__.'/Controller/*Controller.php') as $file) {
                $class = 'OpenSolid\\Api\\Tests\\Fixtures\\Functional\\Controller\\'.basename($file, '.php');
                $container->register($class)
                    ->setAutoconfigured(true)
                    ->addTag('controller.service_arguments');
            }
        });
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/open_solid_api_functional_tests/cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/open_solid_api_functional_tests/log';
    }
}
