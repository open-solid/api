<?php

declare(strict_types=1);

namespace OpenSolid\Api\OpenApi\Processor;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Augments schema properties from Symfony Validator constraints:
 * - `format` from Uuid, Ulid, Email, Url, Hostname, Ip, Date, DateTime, Time
 * - `minLength` / `maxLength` from Length
 * - `minimum` / `maximum` / `exclusiveMinimum` / `exclusiveMaximum` from Range and comparison constraints
 * - `pattern` from Regex
 * - `enum` from Choice
 * - `minItems` / `maxItems` from Count
 */
final readonly class AugmentSchemaConstraints
{
    public function __invoke(Analysis $analysis): void
    {
        /** @var OA\Schema[] $schemas */
        $schemas = $analysis->getAnnotationsOfType(OA\Schema::class);

        foreach ($schemas as $schema) {
            if (Generator::isDefault($schema->properties) || !is_array($schema->properties)) {
                continue;
            }

            foreach ($schema->properties as $property) {
                if (!$property instanceof OA\Property) {
                    continue;
                }

                $reflector = $this->resolveReflectionProperty($property);

                if (!$reflector instanceof \ReflectionProperty) {
                    continue;
                }

                $this->applyConstraints($property, $reflector);
            }
        }
    }

    private function resolveReflectionProperty(OA\Property $property): ?\ReflectionProperty
    {
        $reflector = $property->_context->reflector ?? null;

        if ($reflector instanceof \ReflectionParameter) {
            $class = $reflector->getDeclaringFunction()->getDeclaringClass();
            if ($class?->hasProperty($reflector->getName())) {
                $reflector = $class->getProperty($reflector->getName());
            }
        }

        return $reflector instanceof \ReflectionProperty ? $reflector : null;
    }

    private function applyConstraints(OA\Property $property, \ReflectionProperty $reflector): void
    {
        $attributes = $reflector->getAttributes(Constraint::class, \ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            $constraint = $attribute->newInstance();

            match (true) {
                $constraint instanceof Assert\Uuid => $this->applyFormat($property, 'uuid'),
                $constraint instanceof Assert\Ulid => $this->applyFormat($property, 'ulid'),
                $constraint instanceof Assert\Email => $this->applyFormat($property, 'email'),
                $constraint instanceof Assert\Url => $this->applyFormat($property, 'uri'),
                $constraint instanceof Assert\Hostname => $this->applyFormat($property, 'hostname'),
                $constraint instanceof Assert\Ip => $this->applyIpFormat($property, $constraint),
                $constraint instanceof Assert\Date => $this->applyFormat($property, 'date'),
                $constraint instanceof Assert\DateTime => $this->applyFormat($property, 'date-time'),
                $constraint instanceof Assert\Time => $this->applyFormat($property, 'time'),
                $constraint instanceof Assert\Length => $this->applyLength($property, $constraint),
                $constraint instanceof Assert\Count => $this->applyCount($property, $constraint),
                $constraint instanceof Assert\Range => $this->applyRange($property, $constraint),
                $constraint instanceof Assert\Positive,
                $constraint instanceof Assert\PositiveOrZero,
                $constraint instanceof Assert\Negative,
                $constraint instanceof Assert\NegativeOrZero,
                $constraint instanceof Assert\GreaterThan,
                $constraint instanceof Assert\GreaterThanOrEqual,
                $constraint instanceof Assert\LessThan,
                $constraint instanceof Assert\LessThanOrEqual => $this->applyComparison($property, $constraint),
                $constraint instanceof Assert\Regex => $this->applyPattern($property, $constraint),
                $constraint instanceof Assert\Choice => $this->applyChoice($property, $constraint),
                default => null,
            };
        }
    }

    private function applyFormat(OA\Property $property, string $format): void
    {
        if (Generator::isDefault($property->format)) {
            $property->format = $format;
        }
    }

    private function applyIpFormat(OA\Property $property, Assert\Ip $constraint): void
    {
        if (Generator::isDefault($property->format)) {
            $property->format = str_starts_with($constraint->version, '6') ? 'ipv6' : 'ipv4';
        }
    }

    private function applyLength(OA\Property $property, Assert\Length $constraint): void
    {
        if (null !== $constraint->min && Generator::isDefault($property->minLength)) {
            $property->minLength = $constraint->min;
        }

        if (null !== $constraint->max && Generator::isDefault($property->maxLength)) {
            $property->maxLength = $constraint->max;
        }
    }

    private function applyCount(OA\Property $property, Assert\Count $constraint): void
    {
        if (null !== $constraint->min && Generator::isDefault($property->minItems)) {
            $property->minItems = $constraint->min;
        }

        if (null !== $constraint->max && Generator::isDefault($property->maxItems)) {
            $property->maxItems = $constraint->max;
        }
    }

    private function applyRange(OA\Property $property, Assert\Range $constraint): void
    {
        if (null !== $constraint->min && is_numeric($constraint->min) && Generator::isDefault($property->minimum)) {
            $property->minimum = $constraint->min + 0;
        }

        if (null !== $constraint->max && is_numeric($constraint->max) && Generator::isDefault($property->maximum)) {
            $property->maximum = $constraint->max + 0;
        }
    }

    private function applyComparison(OA\Property $property, Assert\AbstractComparison $constraint): void
    {
        if (!is_numeric($constraint->value)) {
            return;
        }

        $value = $constraint->value + 0;

        match (true) {
            $constraint instanceof Assert\Positive,
            $constraint instanceof Assert\GreaterThan => Generator::isDefault($property->exclusiveMinimum) && $property->exclusiveMinimum = $value,
            $constraint instanceof Assert\PositiveOrZero,
            $constraint instanceof Assert\GreaterThanOrEqual => Generator::isDefault($property->minimum) && $property->minimum = $value,
            $constraint instanceof Assert\Negative,
            $constraint instanceof Assert\LessThan => Generator::isDefault($property->exclusiveMaximum) && $property->exclusiveMaximum = $value,
            $constraint instanceof Assert\NegativeOrZero,
            $constraint instanceof Assert\LessThanOrEqual => Generator::isDefault($property->maximum) && $property->maximum = $value,
            default => null,
        };
    }

    private function applyPattern(OA\Property $property, Assert\Regex $constraint): void
    {
        if (!$constraint->match || !Generator::isDefault($property->pattern)) {
            return;
        }

        $pattern = $constraint->pattern;

        if (null === $pattern || '' === $pattern) {
            return;
        }

        // Strip PHP regex delimiters (e.g., /^pattern$/ → ^pattern$)
        $delimiter = $pattern[0];
        $endPos = strrpos($pattern, $delimiter);

        if ($endPos > 0) {
            $pattern = substr($pattern, 1, $endPos - 1);
        }

        $property->pattern = $pattern;
    }

    private function applyChoice(OA\Property $property, Assert\Choice $constraint): void
    {
        if ($constraint->multiple || null === $constraint->choices || !Generator::isDefault($property->enum)) {
            return;
        }

        $property->enum = $constraint->choices;
    }
}
