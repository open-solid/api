<?php

declare(strict_types=1);

namespace OpenSolid\Api\OpenApi\Processor;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Attributes\PathParameter;
use OpenApi\Context;
use OpenApi\Generator;
use OpenApi\TypeResolverInterface;
use OpenSolid\Api\OpenApi\Resolver\PathParameterSchemaResolver;

/**
 * Fills gaps left by AugmentParameters for parameters backed by a ReflectionParameter or ReflectionProperty:
 * infers schema type, default value, and required flag from the PHP type hint.
 */
final readonly class AugmentQueryParameters
{
    /**
     * @param iterable<PathParameterSchemaResolver> $pathParameterSchemaResolvers
     */
    public function __construct(
        private iterable $pathParameterSchemaResolvers = [],
    ) {
    }

    public function __invoke(Analysis $analysis): void
    {
        /** @var OA\Parameter[] $parameters */
        $parameters = $analysis->getAnnotationsOfType(OA\Parameter::class);

        foreach ($parameters as $parameter) {
            $reflector = $parameter->_context->reflector ?? null;

            if (!$reflector instanceof \ReflectionParameter && !$reflector instanceof \ReflectionProperty) {
                continue;
            }

            if (Generator::isDefault($parameter->schema)) {
                $parameter->schema = new OA\Schema([
                    '_context' => new Context(['generated' => true], $parameter->_context),
                ]);
            }

            $schema = $parameter->schema;
            $type = $reflector->getType();
            $hasDefault = $reflector instanceof \ReflectionParameter
                ? $reflector->isDefaultValueAvailable()
                : $reflector->hasDefaultValue();

            if (Generator::isDefault($schema->type)) {
                $namedType = $type instanceof \ReflectionNamedType ? $type : null;

                if (null !== $namedType && !$namedType->isBuiltin()) {
                    if ($parameter instanceof PathParameter && $reflector instanceof \ReflectionParameter) {
                        foreach ($this->pathParameterSchemaResolvers as $resolver) {
                            if ($resolver->resolve($reflector, $parameter)) {
                                break;
                            }
                        }
                    }

                    $namedType = null;
                }

                if (null !== $namedType) {
                    $nativeName = strtolower($namedType->getName());
                    $mapped = TypeResolverInterface::NATIVE_TYPE_MAP[$nativeName] ?? null;

                    if (is_array($mapped)) {
                        $schema->type = $mapped[0];
                        if (Generator::isDefault($schema->format)) {
                            $schema->format = $mapped[1];
                        }
                    } elseif (is_string($mapped)) {
                        $schema->type = $mapped;
                    }
                }
            }

            if (Generator::isDefault($schema->default) && $hasDefault) {
                $default = $reflector->getDefaultValue();
                if ($default !== null) {
                    $schema->default = $default;
                }
            }

            if (Generator::isDefault($parameter->required) || ($parameter->required && $hasDefault)) {
                $isNullable = $type instanceof \ReflectionNamedType && $type->allowsNull();
                $parameter->required = !$hasDefault && !$isNullable;
            }
        }
    }
}
