<?php

declare(strict_types=1);

namespace OpenSolid\Api\OpenApi\Processor;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

/**
 * Infers request body content from #[MapRequestPayload] and/or #[OA\RequestBody].
 *
 * Supports three scenarios:
 *  1. Only #[MapRequestPayload] — creates a full RequestBody from the parameter type.
 *  2. Only #[OA\RequestBody] — fills in content from the parameter type when not set.
 *  3. Both coexist — uses the explicit OA\RequestBody metadata (e.g. description)
 *     and infers content/required from #[MapRequestPayload]'s parameter type when missing.
 */
final readonly class AugmentRequestBodies
{
    public function __construct(
        private string $mediaType,
    ) {
    }

    public function __invoke(Analysis $analysis): void
    {
        /** @var OA\Operation[] $operations */
        $operations = $analysis->getAnnotationsOfType(OA\Operation::class);

        foreach ($operations as $operation) {
            $reflector = $operation->_context->reflector;

            if (!$reflector instanceof \ReflectionClass || !$reflector->hasMethod('__invoke')) {
                continue;
            }

            $payloadParam = $this->findPayloadParameter($reflector->getMethod('__invoke'));

            if (null === $payloadParam) {
                continue;
            }

            $type = $payloadParam->getType();

            if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
                continue;
            }

            if (Generator::isDefault($operation->requestBody)) {
                // No explicit OA\RequestBody — create one from scratch
                $operation->requestBody = $this->createRequestBody($type, $operation->_context, $analysis);
            } elseif ($operation->requestBody instanceof OA\RequestBody) {
                // Explicit OA\RequestBody exists — fill in missing content/required
                $this->augmentRequestBody($operation->requestBody, $type, $analysis);
            }
        }
    }

    /**
     * Finds the __invoke() parameter annotated with #[MapRequestPayload] or #[OA\RequestBody].
     */
    private function findPayloadParameter(\ReflectionMethod $method): ?\ReflectionParameter
    {
        foreach ($method->getParameters() as $parameter) {
            if ($parameter->getAttributes(MapRequestPayload::class) !== []) {
                return $parameter;
            }
        }

        return null;
    }

    private function createRequestBody(\ReflectionNamedType $type, Context $parentContext, Analysis $analysis): OA\RequestBody
    {
        $context = new Context(['generated' => true], $parentContext);
        $mediaType = $this->createMediaType($type, $context);

        $requestBody = new OA\RequestBody([
            'required' => !$type->allowsNull(),
            'content' => [$mediaType],
            '_context' => $context,
        ]);

        $analysis->addAnnotation($requestBody, $context);
        $analysis->addAnnotation($mediaType, $context);

        return $requestBody;
    }

    private function augmentRequestBody(OA\RequestBody $requestBody, \ReflectionNamedType $type, Analysis $analysis): void
    {
        if (Generator::isDefault($requestBody->content)) {
            $context = new Context(['generated' => true], $requestBody->_context);
            $mediaType = $this->createMediaType($type, $context);
            $requestBody->content = [$mediaType];
            $analysis->addAnnotation($mediaType, $context);
        }

        if (Generator::isDefault($requestBody->required)) {
            $requestBody->required = !$type->allowsNull();
        }
    }

    private function createMediaType(\ReflectionNamedType $type, Context $context): OA\MediaType
    {
        return new OA\MediaType([
            'mediaType' => $this->mediaType,
            'schema' => new OA\Schema([
                'ref' => $type->getName(),
                '_context' => $context,
            ]),
            '_context' => $context,
        ]);
    }
}
