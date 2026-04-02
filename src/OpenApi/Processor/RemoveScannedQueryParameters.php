<?php

declare(strict_types=1);

namespace OpenSolid\Api\OpenApi\Processor;

use OpenApi\Analysis;
use OpenApi\Attributes as OAT;

/**
 * Removes QueryParameter annotations that swagger-php's scanner picks up from class properties.
 *
 * These scanned annotations have nested=false in their context and a ReflectionProperty reflector.
 * Without removal, MergeIntoComponents promotes them into Components::parameters where they fail
 * validation (missing parameter key-field, missing name). They're not needed because
 * AugmentQueryParameterSets re-creates them from reflection and attaches them directly to operations.
 */
final readonly class RemoveScannedQueryParameters
{
    public function __invoke(Analysis $analysis): void
    {
        foreach ($analysis->getAnnotationsOfType(OAT\QueryParameter::class) as $annotation) {
            if ($annotation->_context->nested) {
                continue;
            }

            if (($annotation->_context->reflector ?? null) instanceof \ReflectionProperty) {
                $analysis->annotations->offsetUnset($annotation);
            }
        }
    }
}
