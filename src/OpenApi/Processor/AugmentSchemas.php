<?php

declare(strict_types=1);

namespace OpenSolid\Api\OpenApi\Processor;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Generator;

/**
 * Augments schemas from PHP reflection:
 * - `readOnly` from `readonly` class keyword
 * - `required` fields from property nullability and default values
 */
final readonly class AugmentSchemas
{
    public function __invoke(Analysis $analysis): void
    {
        /** @var OA\Schema[] $schemas */
        $schemas = $analysis->getAnnotationsOfType(OA\Schema::class);

        foreach ($schemas as $schema) {
            if (!Generator::isDefault($schema->required)) {
                continue;
            }

            if (Generator::isDefault($schema->properties) || !is_array($schema->properties)) {
                continue;
            }

            $required = [];

            foreach ($schema->properties as $property) {
                if (!$property instanceof OA\Property) {
                    continue;
                }

                $reflector = $property->_context->reflector ?? null;

                if ($reflector instanceof \ReflectionParameter) {
                    // Promoted constructor properties: resolve to the actual property
                    $class = $reflector->getDeclaringFunction()->getDeclaringClass();
                    if ($class?->hasProperty($reflector->getName())) {
                        $reflector = $class->getProperty($reflector->getName());
                    }
                }

                if (!$reflector instanceof \ReflectionProperty) {
                    continue;
                }

                $type = $reflector->getType();
                $isNullable = $type === null || $type->allowsNull();
                $hasDefault = $reflector->hasDefaultValue();

                if (!$isNullable && !$hasDefault) {
                    $propertyName = Generator::isDefault($property->property)
                        ? $reflector->getName()
                        : $property->property;

                    $required[] = $propertyName;
                }
            }

            if ($required !== []) {
                $schema->required = $required;
            }
        }
    }
}
