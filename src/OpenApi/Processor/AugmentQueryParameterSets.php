<?php

declare(strict_types=1);

namespace OpenSolid\Api\OpenApi\Processor;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAT;
use OpenApi\Context;
use OpenApi\Generator;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;

/**
 * Expands parameters annotated with #[MapQueryString] into individual query parameters.
 *
 * When the __invoke() method of an operation class has a parameter annotated with
 * #[MapQueryString], this processor reflects on the target class properties to find
 * #[OA\QueryParameter] attributes and adds them as individual query parameters
 * on the operation.
 */
final readonly class AugmentQueryParameterSets
{
    public function __invoke(Analysis $analysis): void
    {
        /** @var OA\Operation[] $operations */
        $operations = $analysis->getAnnotationsOfType(OA\Operation::class);

        foreach ($operations as $operation) {
            $reflector = $operation->_context->reflector;

            if (!$reflector instanceof \ReflectionClass || !$reflector->hasMethod('__invoke')) {
                continue;
            }

            foreach ($reflector->getMethod('__invoke')->getParameters() as $parameter) {
                if ([] === $parameter->getAttributes(MapQueryString::class)) {
                    continue;
                }

                $type = $parameter->getType();

                if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
                    continue;
                }

                $className = $type->getName();

                if (!class_exists($className)) {
                    continue;
                }

                $class = new \ReflectionClass($className);

                if (Generator::isDefault($operation->parameters)) {
                    $operation->parameters = [];
                }

                foreach ($class->getProperties() as $property) {
                    $attributes = $property->getAttributes(OAT\QueryParameter::class, \ReflectionAttribute::IS_INSTANCEOF);

                    if ($attributes === []) {
                        continue;
                    }

                    /** @var OAT\QueryParameter $queryParam */
                    $queryParam = $attributes[0]->newInstance();
                    $queryParam->_context = new Context([
                        'nested' => true,
                        'property' => $property->getName(),
                        'reflector' => $property,
                    ], $operation->_context);

                    if (Generator::isDefault($queryParam->name)) {
                        $queryParam->name = $property->getName();
                    }

                    $operation->parameters[] = $queryParam;
                    $analysis->addAnnotation($queryParam, $queryParam->_context);
                }
            }
        }
    }
}
