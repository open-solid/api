<?php

declare(strict_types=1);

namespace OpenSolid\Api\OpenApi\Processor;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;
use OpenSolid\Api\Controller\Model\Paginator\Paginator;
use OpenSolid\Api\OpenApi\Schema\PaginatorSchema;
use OpenSolid\Api\Routing\Attribute\ApiRoute;
use OpenSolid\Core\Domain\Model\GetOrCreateResource;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\GenericType;
use Symfony\Component\TypeInfo\Type\ObjectType;
use Symfony\Component\TypeInfo\TypeResolver\TypeResolverInterface;

/**
 * Infers OA\Operation responses from the Symfony route attribute declared on the action class.
 */
final readonly class AugmentOperations
{
    public function __construct(
        private string $mediaType,
        private TypeResolverInterface $typeResolver,
    ) {
    }

    public function __invoke(Analysis $analysis): void
    {
        /** @var OA\Operation[] $operations */
        $operations = $analysis->getAnnotationsOfType(OA\Operation::class);

        foreach ($operations as $operation) {
            if (!Generator::isDefault($operation->responses)) {
                continue;
            }

            $class = $this->getDeclaringClass($operation);

            if (null === $class) {
                continue;
            }

            $route = $this->findMatchingRoute($class, $operation);

            if (null === $route) {
                continue;
            }

            $method = $class->getMethod('__invoke');
            $statusCode = $route->defaults['_api_status_code'] ?? 200;
            $returnType = $method->getReturnType();

            if ($returnType instanceof \ReflectionNamedType && is_a($returnType->getName(), Response::class, true)) {
                continue;
            }

            $context = new Context(['generated' => true], $operation->_context);

            if ($returnType instanceof \ReflectionNamedType && GetOrCreateResource::class === $returnType->getName()) {
                $operation->responses = $this->createGetOrCreateResponses($method, $context, $analysis);
            } else {
                $paginationEnabled = $route->defaults['_api_pagination'] ?? false;
                $operation->responses = [$this->createResponse($method, $returnType, $statusCode, $context, $analysis, $paginationEnabled)];
            }
        }
    }

    private function getDeclaringClass(OA\Operation $operation): ?\ReflectionClass
    {
        $reflector = $operation->_context->reflector;

        return match (true) {
            $reflector instanceof \ReflectionClass => $reflector,
            $reflector instanceof \ReflectionMethod => $reflector->getDeclaringClass(),
            default => null,
        };
    }

    private function findMatchingRoute(\ReflectionClass $class, OA\Operation $operation): ?ApiRoute
    {
        $attributes = $class->getAttributes(ApiRoute::class, \ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            /** @var ApiRoute $route */
            $route = $attribute->newInstance();

            if (strtolower($route::getMethod()) === $operation->method) {
                return $route;
            }
        }

        return null;
    }

    private function createResponse(\ReflectionMethod $reflector, ?\ReflectionType $returnType, int $statusCode, Context $context, Analysis $analysis, bool $paginationEnabled): OA\Response
    {
        $isVoid = null === $returnType
            || ($returnType instanceof \ReflectionNamedType && 'void' === $returnType->getName());

        if ($isVoid) {
            return new OA\Response([
                'response' => $statusCode,
                'description' => '',
                '_context' => $context,
            ]);
        }

        $schema = $this->resolveSchema($reflector, $context, $paginationEnabled);

        $mediaType = new OA\MediaType([
            'mediaType' => $this->mediaType,
            'schema' => $schema,
            '_context' => $context,
        ]);

        $response = new OA\Response([
            'response' => $statusCode,
            'description' => '',
            'content' => [$mediaType],
            '_context' => $context,
        ]);

        $analysis->addAnnotation($mediaType, $context);

        return $response;
    }

    /**
     * @return OA\Response[]
     */
    private function createGetOrCreateResponses(\ReflectionMethod $reflector, Context $context, Analysis $analysis): array
    {
        $resolvedType = $this->typeResolver->resolve($reflector);

        if ($resolvedType instanceof GenericType
            && $resolvedType->getWrappedType() instanceof ObjectType
            && GetOrCreateResource::class === $resolvedType->getWrappedType()->getClassName()
            && ($variableTypes = $resolvedType->getVariableTypes())
            && $variableTypes[0] instanceof ObjectType
        ) {
            $ref = $variableTypes[0]->getClassName();
        } else {
            $ref = null;
        }

        $responses = [];

        foreach ([Response::HTTP_OK, Response::HTTP_CREATED] as $statusCode) {
            if (null !== $ref) {
                $schema = new OA\Schema(['ref' => $ref, '_context' => $context]);
                $mediaType = new OA\MediaType([
                    'mediaType' => $this->mediaType,
                    'schema' => $schema,
                    '_context' => $context,
                ]);
                $response = new OA\Response([
                    'response' => $statusCode,
                    'description' => '',
                    'content' => [$mediaType],
                    '_context' => $context,
                ]);
                $analysis->addAnnotation($mediaType, $context);
            } else {
                $response = new OA\Response([
                    'response' => $statusCode,
                    'description' => '',
                    '_context' => $context,
                ]);
            }

            $responses[] = $response;
        }

        return $responses;
    }

    private function resolveSchema(\ReflectionMethod $reflector, Context $context, bool $paginationEnabled): OA\Schema
    {
        $resolvedType = $this->typeResolver->resolve($reflector);

        if ($paginationEnabled
            && $resolvedType instanceof CollectionType
            && $resolvedType->getWrappedType() instanceof GenericType
            && $resolvedType->getWrappedType()->getWrappedType() instanceof ObjectType
            && is_a($resolvedType->getWrappedType()->getWrappedType()->getClassName(), Paginator::class, true)
            && ($variableTypes = $resolvedType->getWrappedType()->getVariableTypes())
            && $variableTypes[1] instanceof ObjectType
        ) {
            return new PaginatorSchema($variableTypes[1]->getClassName());
        }

        /** @var \ReflectionNamedType $nativeType */
        $nativeType = $reflector->getReturnType();

        return new OA\Schema([
            'ref' => $nativeType->getName(),
            '_context' => $context,
        ]);
    }
}
