<?php

declare(strict_types=1);

namespace OpenSolid\Api\OpenApi\Processor;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenSolid\Api\Routing\Attribute\ApiRoute;

/**
 * Generates OA\Operation instances from ApiRoute attributes declared on action classes,
 * eliminating the need for duplicate OA\Get/Post/Put/Patch/Delete class-level attributes.
 */
final readonly class GenerateOperationsFromApiRoutes
{
    private const array METHOD_TO_OPERATION = [
        'GET' => OA\Get::class,
        'POST' => OA\Post::class,
        'PUT' => OA\Put::class,
        'PATCH' => OA\Patch::class,
        'DELETE' => OA\Delete::class,
    ];

    /**
     * @param string[] $sourcePaths Paths scanned by the OpenAPI generator, used to
     *                              discover action classes that have no OA annotations.
     */
    public function __construct(
        private array $sourcePaths = [],
    ) {
    }

    public function __invoke(Analysis $analysis): void
    {
        $classes = $this->collectClasses($analysis);

        foreach ($classes as $class) {
            $attributes = $class->getAttributes(ApiRoute::class, \ReflectionAttribute::IS_INSTANCEOF);

            foreach ($attributes as $attribute) {
                /** @var ApiRoute $route */
                $route = $attribute->newInstance();
                $method = $route::getMethod();
                $operationClass = self::METHOD_TO_OPERATION[$method] ?? null;

                if (null === $operationClass) {
                    continue;
                }

                if ($this->hasOperationForMethod($analysis, $class, strtolower($method))) {
                    continue;
                }

                $context = new Context(['reflector' => $class]);
                $path = $route->path;

                $properties = [
                    'path' => \is_array($path) ? $path[0] ?? '' : $path,
                    'operationId' => $route->name,
                    '_context' => $context,
                ];

                if (null !== $route->description) {
                    $properties['description'] = $route->description;
                }

                if (null !== $route->summary) {
                    $properties['summary'] = $route->summary;
                }

                if ([] !== $route->tags) {
                    $properties['tags'] = $route->tags;
                }

                if ($route->deprecated) {
                    $properties['deprecated'] = true;
                }

                $operation = new $operationClass($properties);

                $analysis->addAnnotation($operation, $context);
            }
        }
    }

    /**
     * @return \ReflectionClass[]
     */
    private function collectClasses(Analysis $analysis): array
    {
        $classes = [];

        foreach ($analysis->annotations as $annotation) {
            $class = $this->getDeclaringClass($annotation);

            if (null !== $class && !isset($classes[$class->getName()])) {
                $classes[$class->getName()] = $class;
            }
        }

        // Discover action classes that have no OA annotations but do have ApiRoute attributes.
        // Only considers classes whose source file falls within the configured source paths.
        $realSourcePaths = array_map(realpath(...), $this->sourcePaths);

        foreach (get_declared_classes() as $className) {
            if (isset($classes[$className])) {
                continue;
            }

            $class = new \ReflectionClass($className);
            $file = $class->getFileName();

            if (false === $file || !$this->isWithinSourcePaths($file, $realSourcePaths)) {
                continue;
            }

            if ($class->getAttributes(ApiRoute::class, \ReflectionAttribute::IS_INSTANCEOF) !== []) {
                $classes[$className] = $class;
            }
        }

        return $classes;
    }

    private function getDeclaringClass(OA\AbstractAnnotation $annotation): ?\ReflectionClass
    {
        $reflector = $annotation->_context->reflector ?? null;

        return match (true) {
            $reflector instanceof \ReflectionClass => $reflector,
            $reflector instanceof \ReflectionMethod => $reflector->getDeclaringClass(),
            $reflector instanceof \ReflectionParameter => $reflector->getDeclaringClass(),
            $reflector instanceof \ReflectionProperty => $reflector->getDeclaringClass(),
            default => null,
        };
    }

    /**
     * @param string[] $realSourcePaths
     */
    private function isWithinSourcePaths(string $file, array $realSourcePaths): bool
    {
        foreach ($realSourcePaths as $sourcePath) {
            if (false !== $sourcePath && str_starts_with($file, $sourcePath)) {
                return true;
            }
        }

        return false;
    }

    private function hasOperationForMethod(Analysis $analysis, \ReflectionClass $class, string $method): bool
    {
        /** @var OA\Operation[] $operations */
        $operations = $analysis->getAnnotationsOfType(OA\Operation::class);

        foreach ($operations as $operation) {
            $reflector = $operation->_context->reflector ?? null;

            if ($reflector instanceof \ReflectionClass
                && $reflector->getName() === $class->getName()
                && $operation->method === $method
            ) {
                return true;
            }
        }

        return false;
    }
}
