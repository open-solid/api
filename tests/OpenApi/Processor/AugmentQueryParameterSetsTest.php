<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\OpenApi\Processor;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAT;
use OpenApi\Context;
use OpenApi\Generator;
use OpenSolid\Api\OpenApi\Processor\AugmentQueryParameterSets;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;

class AugmentQueryParameterSetsTest extends TestCase
{
    private AugmentQueryParameterSets $processor;

    protected function setUp(): void
    {
        $this->processor = new AugmentQueryParameterSets();
    }

    #[Test]
    public function itExpandsQueryParametersFromMapQueryString(): void
    {
        $operation = $this->createOperation(ActionWithMapQueryString::class);

        $this->process($operation);

        self::assertIsArray($operation->parameters);
        self::assertCount(2, $operation->parameters);

        self::assertSame('name', $operation->parameters[0]->name);
        self::assertSame('query', $operation->parameters[0]->in);
        self::assertSame('Filter by name', $operation->parameters[0]->description);

        self::assertSame('page', $operation->parameters[1]->name);
        self::assertSame('query', $operation->parameters[1]->in);
        self::assertSame('Page number', $operation->parameters[1]->description);
    }

    #[Test]
    public function itSkipsPropertiesWithoutQueryParameterAttribute(): void
    {
        $operation = $this->createOperation(ActionWithPartialParams::class);

        $this->process($operation);

        self::assertIsArray($operation->parameters);
        self::assertCount(1, $operation->parameters);
        self::assertSame('name', $operation->parameters[0]->name);
    }

    #[Test]
    public function itPreservesExistingParameters(): void
    {
        $operation = $this->createOperation(ActionWithMapQueryString::class);

        // Add an existing path parameter
        $existingParam = new OA\Parameter([
            'name' => 'id',
            'in' => 'path',
            '_context' => $operation->_context,
        ]);
        $operation->parameters = [$existingParam];

        $this->process($operation);

        self::assertCount(3, $operation->parameters);
        self::assertSame('id', $operation->parameters[0]->name);
        self::assertSame('path', $operation->parameters[0]->in);
        self::assertSame('name', $operation->parameters[1]->name);
        self::assertSame('page', $operation->parameters[2]->name);
    }

    #[Test]
    public function itSkipsOperationWithoutMapQueryString(): void
    {
        $operation = $this->createOperation(ActionWithoutQueryString::class);

        $this->process($operation);

        self::assertTrue(Generator::isDefault($operation->parameters));
    }

    #[Test]
    public function itSkipsBuiltinParameterType(): void
    {
        $operation = $this->createOperation(ActionWithBuiltinQueryString::class);

        $this->process($operation);

        self::assertTrue(Generator::isDefault($operation->parameters));
    }

    #[Test]
    public function itRemovesOriginallyScannedAnnotationsFromAnalysis(): void
    {
        $operation = $this->createOperation(ActionWithMapQueryString::class);

        // Simulate swagger-php scanning: add QueryParameter annotations with non-nested context
        $class = new \ReflectionClass(QueryStringParams::class);
        foreach ($class->getProperties() as $property) {
            $attrs = $property->getAttributes(OAT\QueryParameter::class, \ReflectionAttribute::IS_INSTANCEOF);
            if ($attrs !== []) {
                $scanned = $attrs[0]->newInstance();
                $scanned->_context = new Context([
                    'property' => $property->getName(),
                    'reflector' => $property,
                ]);
                $analysis = new Analysis([$operation, $scanned], new Context());
            }
        }

        ($this->processor)($analysis);

        // The originally-scanned (non-nested) QueryParameter annotations should be removed
        $remaining = [];
        foreach ($analysis->getAnnotationsOfType(OAT\QueryParameter::class) as $annotation) {
            $remaining[] = $annotation;
        }

        // Only the new instances (nested => true) should remain
        foreach ($remaining as $annotation) {
            self::assertTrue($annotation->_context->is('nested'), 'Expected all remaining QueryParameter annotations to be nested');
        }
    }

    #[Test]
    public function itUsesCustomParameterName(): void
    {
        $operation = $this->createOperation(ActionWithCustomParamName::class);

        $this->process($operation);

        self::assertIsArray($operation->parameters);
        self::assertCount(1, $operation->parameters);
        self::assertSame('filter_name', $operation->parameters[0]->name);
    }

    private function createOperation(string $className): OA\Get
    {
        $reflector = new \ReflectionClass($className);
        $context = new Context(['reflector' => $reflector]);

        return new OA\Get(['_context' => $context]);
    }

    private function process(OA\Operation $operation): void
    {
        $analysis = new Analysis([$operation], new Context());
        ($this->processor)($analysis);
    }
}

// Test fixtures

class QueryStringParams
{
    #[OAT\QueryParameter(description: 'Filter by name')]
    public ?string $name = null;

    #[OAT\QueryParameter(description: 'Page number')]
    public int $page = 1;
}

class PartialQueryStringParams
{
    #[OAT\QueryParameter(description: 'Filter by name')]
    public ?string $name = null;

    public string $internal = 'hidden';
}

class CustomNameParams
{
    #[OAT\QueryParameter(name: 'filter_name', description: 'Custom filter')]
    public ?string $name = null;
}

class ActionWithMapQueryString
{
    public function __invoke(#[MapQueryString] QueryStringParams $params): void
    {
    }
}

class ActionWithPartialParams
{
    public function __invoke(#[MapQueryString] PartialQueryStringParams $params): void
    {
    }
}

class ActionWithoutQueryString
{
    public function __invoke(string $id): void
    {
    }
}

class ActionWithBuiltinQueryString
{
    public function __invoke(#[MapQueryString] string $params): void
    {
    }
}

class ActionWithCustomParamName
{
    public function __invoke(#[MapQueryString] CustomNameParams $params): void
    {
    }
}
