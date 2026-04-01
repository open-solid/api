<?php

declare(strict_types=1);

namespace OpenSolid\Api\OpenApi\Processor;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAT;
use OpenApi\Generator;

/**
 * Merges orphaned parameter annotations from __invoke() method parameters
 * into class-level OA\Operation annotations.
 *
 * This is needed because swagger-php's AttributeAnnotationFactory only merges
 * parameter attributes into operations when both are found on the same reflector.
 * When the operation is on the class and parameters are on the method, they end up
 * as separate, unlinked annotations in the Analysis.
 */
final readonly class MergeMethodAnnotationsIntoOperations
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

            $method = $reflector->getMethod('__invoke');

            $this->mergeParameters($analysis, $operation, $method);
            $this->mergeRequestBody($analysis, $operation, $method);
        }
    }

    private function mergeParameters(Analysis $analysis, OA\Operation $operation, \ReflectionMethod $method): void
    {
        /** @var OAT\Parameter[] $parameters */
        $parameters = $analysis->getAnnotationsOfType(OAT\Parameter::class);

        foreach ($parameters as $parameter) {
            $paramReflector = $parameter->_context->reflector ?? null;

            if (!$paramReflector instanceof \ReflectionParameter) {
                continue;
            }

            if ($paramReflector->getDeclaringFunction()->getName() !== $method->getName()
                || $paramReflector->getDeclaringClass()?->getName() !== $method->getDeclaringClass()->getName()
            ) {
                continue;
            }

            if (Generator::isDefault($operation->parameters)) {
                $operation->parameters = [];
            }

            $operation->parameters[] = $parameter;
        }
    }

    private function mergeRequestBody(Analysis $analysis, OA\Operation $operation, \ReflectionMethod $method): void
    {
        /** @var OAT\RequestBody[] $requestBodies */
        $requestBodies = $analysis->getAnnotationsOfType(OAT\RequestBody::class);

        foreach ($requestBodies as $requestBody) {
            $paramReflector = $requestBody->_context->reflector ?? null;

            if (!$paramReflector instanceof \ReflectionParameter) {
                continue;
            }

            if ($paramReflector->getDeclaringFunction()->getName() !== $method->getName()
                || $paramReflector->getDeclaringClass()?->getName() !== $method->getDeclaringClass()->getName()
            ) {
                continue;
            }

            if (Generator::isDefault($operation->requestBody)) {
                $operation->requestBody = $requestBody;
            }
        }
    }
}
