<?php

declare(strict_types=1);

namespace OpenSolid\Api\OpenApi;

use OpenApi\Generator;
use OpenApi\Pipeline;
use OpenApi\Processors\AugmentParameters;
use OpenApi\Processors\AugmentRefs;
use OpenApi\Processors\AugmentRequestBody;
use OpenApi\Processors\BuildPaths;
use OpenSolid\Api\OpenApi\Processor\AugmentOperations;
use OpenSolid\Api\OpenApi\Processor\AugmentQueryParameters;
use OpenSolid\Api\OpenApi\Processor\AugmentQueryParameterSets;
use OpenSolid\Api\OpenApi\Processor\AugmentRequestBodies;
use OpenSolid\Api\OpenApi\Processor\AugmentSchemaConstraints;
use OpenSolid\Api\OpenApi\Processor\AugmentSchemas;
use OpenSolid\Api\OpenApi\Processor\GenerateOperationsFromApiRoutes;
use OpenSolid\Api\OpenApi\Processor\MergeMethodAnnotationsIntoOperations;
use Symfony\Component\Validator\Constraint;
use Psr\Log\LoggerInterface;
use Symfony\Component\TypeInfo\TypeResolver\TypeResolverInterface;

final readonly class OpenApiGeneratorFactory
{
    public function __construct(
        private LoggerInterface $logger,
        private TypeResolverInterface $typeResolver,
        private array $config,
        private iterable $pathParameterSchemaResolvers = [],
    ) {
    }

    public function __invoke(): OpenApiGenerator
    {
        $generator = new Generator($this->logger)
            ->setVersion($this->config['version'])
            ->setConfig($this->config['config'])
            ->withProcessorPipeline(function (Pipeline $pl) {
                $pl->insert(new GenerateOperationsFromApiRoutes($this->config['paths']), BuildPaths::class);
                $pl->insert(new MergeMethodAnnotationsIntoOperations(), BuildPaths::class);
                $pl->insert(new AugmentOperations($this->config['media_type'], $this->typeResolver), BuildPaths::class);
                $pl->insert(new AugmentRequestBodies($this->config['media_type']), BuildPaths::class);
                $pl->insert(new AugmentQueryParameterSets(), AugmentParameters::class);
                $pl->insert(new AugmentQueryParameters($this->pathParameterSchemaResolvers), AugmentRefs::class);
                $pl->insert(new AugmentSchemas(), AugmentRequestBody::class);
                if (class_exists(Constraint::class)) {
                    $pl->insert(new AugmentSchemaConstraints(), AugmentRequestBody::class);
                }
            });

        return new OpenApiGenerator($generator, $this->config);
    }
}
