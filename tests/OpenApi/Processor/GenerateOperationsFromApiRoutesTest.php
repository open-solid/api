<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\OpenApi\Processor;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenSolid\Api\OpenApi\Processor\GenerateOperationsFromApiRoutes;
use OpenSolid\Api\Routing\Attribute\Delete;
use OpenSolid\Api\Routing\Attribute\Get;
use OpenSolid\Api\Routing\Attribute\Patch;
use OpenSolid\Api\Routing\Attribute\Post;
use OpenSolid\Api\Routing\Attribute\Put;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GenerateOperationsFromApiRoutesTest extends TestCase
{
    private GenerateOperationsFromApiRoutes $processor;

    protected function setUp(): void
    {
        $this->processor = new GenerateOperationsFromApiRoutes();
    }

    #[Test]
    public function itGeneratesGetOperation(): void
    {
        $analysis = $this->createAnalysisForClass(RouteActionWithGet::class);

        ($this->processor)($analysis);

        $operations = $analysis->getAnnotationsOfType(OA\Get::class);
        self::assertCount(1, $operations);
        self::assertSame('/items/{id}', $operations[0]->path);
        self::assertSame('api_get_item', $operations[0]->operationId);
        self::assertSame('Get an item', $operations[0]->description);
        self::assertSame('Retrieves a single item.', $operations[0]->summary);
        self::assertSame(['Item'], $operations[0]->tags);
    }

    #[Test]
    public function itGeneratesPostOperation(): void
    {
        $analysis = $this->createAnalysisForClass(RouteActionWithPost::class);

        ($this->processor)($analysis);

        $operations = $analysis->getAnnotationsOfType(OA\Post::class);
        self::assertCount(1, $operations);
        self::assertSame('/items', $operations[0]->path);
        self::assertSame('api_create_item', $operations[0]->operationId);
    }

    #[Test]
    public function itGeneratesPutOperation(): void
    {
        $analysis = $this->createAnalysisForClass(RouteActionWithPut::class);

        ($this->processor)($analysis);

        $operations = $analysis->getAnnotationsOfType(OA\Put::class);
        self::assertCount(1, $operations);
        self::assertSame('/items/{id}', $operations[0]->path);
    }

    #[Test]
    public function itGeneratesPatchOperation(): void
    {
        $analysis = $this->createAnalysisForClass(RouteActionWithPatch::class);

        ($this->processor)($analysis);

        $operations = $analysis->getAnnotationsOfType(OA\Patch::class);
        self::assertCount(1, $operations);
        self::assertSame('/items/{id}', $operations[0]->path);
    }

    #[Test]
    public function itGeneratesDeleteOperation(): void
    {
        $analysis = $this->createAnalysisForClass(RouteActionWithDelete::class);

        ($this->processor)($analysis);

        $operations = $analysis->getAnnotationsOfType(OA\Delete::class);
        self::assertCount(1, $operations);
        self::assertSame('/items/{id}', $operations[0]->path);
    }

    #[Test]
    public function itSetsDeprecatedFlag(): void
    {
        $analysis = $this->createAnalysisForClass(RouteActionDeprecated::class);

        ($this->processor)($analysis);

        $operations = $analysis->getAnnotationsOfType(OA\Get::class);
        self::assertCount(1, $operations);
        self::assertTrue($operations[0]->deprecated);
    }

    #[Test]
    public function itSkipsClassWithoutApiRoute(): void
    {
        $analysis = $this->createAnalysisForClass(RouteActionWithoutRoute::class);

        ($this->processor)($analysis);

        $operations = $analysis->getAnnotationsOfType(OA\Operation::class);
        self::assertCount(0, $operations);
    }

    #[Test]
    public function itDoesNotDuplicateExistingOperation(): void
    {
        $class = new \ReflectionClass(RouteActionWithGet::class);
        $context = new Context(['reflector' => $class]);
        $existing = new OA\Get(['path' => '/items/{id}', 'operationId' => 'existing', '_context' => $context]);

        $analysis = new Analysis([$existing], new Context());

        ($this->processor)($analysis);

        $operations = $analysis->getAnnotationsOfType(OA\Get::class);
        self::assertCount(1, $operations);
        self::assertSame('existing', $operations[0]->operationId);
    }

    #[Test]
    public function itSetsContextReflectorToReflectionClass(): void
    {
        $analysis = $this->createAnalysisForClass(RouteActionWithGet::class);

        ($this->processor)($analysis);

        $operations = $analysis->getAnnotationsOfType(OA\Get::class);
        self::assertCount(1, $operations);
        self::assertInstanceOf(\ReflectionClass::class, $operations[0]->_context->reflector);
        self::assertSame(RouteActionWithGet::class, $operations[0]->_context->reflector->getName());
    }

    private function createAnalysisForClass(string $className): Analysis
    {
        $class = new \ReflectionClass($className);
        $context = new Context(['reflector' => $class]);

        // Create a dummy annotation to seed the analysis with this class
        $schema = new OA\Schema(['_context' => $context]);

        return new Analysis([$schema], new Context());
    }
}

// Test fixtures

#[Get(
    path: '/items/{id}',
    name: 'api_get_item',
    description: 'Get an item',
    summary: 'Retrieves a single item.',
    tags: ['Item'],
)]
class RouteActionWithGet
{
    public function __invoke(): void
    {
    }
}

#[Post(path: '/items', name: 'api_create_item')]
class RouteActionWithPost
{
    public function __invoke(): void
    {
    }
}

#[Put(path: '/items/{id}', name: 'api_replace_item')]
class RouteActionWithPut
{
    public function __invoke(): void
    {
    }
}

#[Patch(path: '/items/{id}', name: 'api_update_item')]
class RouteActionWithPatch
{
    public function __invoke(): void
    {
    }
}

#[Delete(path: '/items/{id}', name: 'api_delete_item')]
class RouteActionWithDelete
{
    public function __invoke(): void
    {
    }
}

#[Get(path: '/old-items', name: 'api_old_items', deprecated: true)]
class RouteActionDeprecated
{
    public function __invoke(): void
    {
    }
}

class RouteActionWithoutRoute
{
    public function __invoke(): void
    {
    }
}
