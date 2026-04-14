<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\OpenApi\Processor;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;
use OpenSolid\Api\OpenApi\Processor\AugmentOperations;
use OpenSolid\Api\Routing\Attribute\Delete;
use OpenSolid\Api\Routing\Attribute\Get;
use OpenSolid\Api\Routing\Attribute\Post;
use OpenSolid\Api\Routing\Attribute\Put;
use OpenSolid\Core\Domain\Model\GetOrCreateResource;
use OpenSolid\Core\Domain\Repository\InMemoryCollection;
use OpenSolid\Core\Domain\Repository\Paginator;
use OpenSolid\Core\Domain\Repository\SelectablePaginator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\TypeInfo\TypeResolver\TypeResolver;

class AugmentOperationsTest extends TestCase
{
    private AugmentOperations $processor;

    protected function setUp(): void
    {
        $this->processor = new AugmentOperations('application/json', TypeResolver::create());
    }

    #[Test]
    public function itInfersResponseWithObjectReturnType(): void
    {
        $operation = $this->createOperation('get', ActionWithGet::class);

        $this->process($operation);

        self::assertIsArray($operation->responses);
        self::assertCount(1, $operation->responses);

        $response = $operation->responses[0];
        self::assertSame(200, $response->response);
        self::assertIsArray($response->content);
        self::assertSame('application/json', $response->content[0]->mediaType);
    }

    #[Test]
    public function itInfersResponseForVoidReturnType(): void
    {
        $operation = $this->createOperation('delete', ActionWithDelete::class);

        $this->process($operation);

        self::assertIsArray($operation->responses);
        self::assertCount(1, $operation->responses);

        $response = $operation->responses[0];
        self::assertSame(204, $response->response);
        self::assertTrue(Generator::isDefault($response->content));
    }

    #[Test]
    public function itInfersStatusCode204ForVoidReturnTypeRegardlessOfMethod(): void
    {
        $operation = $this->createOperation('put', ActionWithVoidPut::class);

        $this->process($operation);

        self::assertIsArray($operation->responses);
        self::assertCount(1, $operation->responses);

        $response = $operation->responses[0];
        self::assertSame(204, $response->response);
        self::assertTrue(Generator::isDefault($response->content));
    }

    #[Test]
    public function itRespectsExplicitStatusCodeForVoidReturnType(): void
    {
        $operation = $this->createOperation('delete', ActionWithVoidAndExplicitStatusCode::class);

        $this->process($operation);

        self::assertIsArray($operation->responses);
        self::assertCount(1, $operation->responses);

        $response = $operation->responses[0];
        self::assertSame(202, $response->response);
        self::assertTrue(Generator::isDefault($response->content));
    }

    #[Test]
    public function itInfersStatusCode201ForPost(): void
    {
        $operation = $this->createOperation('post', ActionWithPost::class);

        $this->process($operation);

        self::assertIsArray($operation->responses);
        $response = $operation->responses[0];
        self::assertSame(201, $response->response);
    }

    #[Test]
    public function itInfersResponseForPaginatorReturnType(): void
    {
        $operation = $this->createOperation('get', ActionWithPaginator::class);

        $this->process($operation);

        self::assertIsArray($operation->responses);
        self::assertCount(1, $operation->responses);

        $response = $operation->responses[0];
        self::assertSame(200, $response->response);
        self::assertIsArray($response->content);
        self::assertSame('application/json', $response->content[0]->mediaType);
    }

    #[Test]
    public function itSkipsResponseInferenceForSymfonyResponseReturnType(): void
    {
        $operation = $this->createOperation('get', ActionWithJsonResponse::class);

        $this->process($operation);

        self::assertTrue(Generator::isDefault($operation->responses));
    }

    #[Test]
    public function itDoesNotOverrideExplicitResponses(): void
    {
        $operation = $this->createOperation('get', ActionWithGet::class);
        $operation->responses = [new OA\Response(['response' => 418, '_context' => $operation->_context])];

        $this->process($operation);

        self::assertCount(1, $operation->responses);
        self::assertSame(418, $operation->responses[0]->response);
    }

    #[Test]
    public function itInfersTwoResponsesForGetOrCreateResourceReturnType(): void
    {
        $operation = $this->createOperation('put', ActionWithGetOrCreate::class);

        $this->process($operation);

        self::assertIsArray($operation->responses);
        self::assertCount(2, $operation->responses);

        $okResponse = $operation->responses[0];
        self::assertSame(200, $okResponse->response);
        self::assertIsArray($okResponse->content);
        self::assertSame('application/json', $okResponse->content[0]->mediaType);

        $createdResponse = $operation->responses[1];
        self::assertSame(201, $createdResponse->response);
        self::assertIsArray($createdResponse->content);
        self::assertSame('application/json', $createdResponse->content[0]->mediaType);
    }

    #[Test]
    public function itSkipsOperationWithoutMatchingRoute(): void
    {
        $operation = $this->createOperation('get', ActionWithoutRoute::class);

        $this->process($operation);

        self::assertTrue(Generator::isDefault($operation->path));
    }

    private function createOperation(string $method, string $className): OA\Operation
    {
        $reflector = new \ReflectionClass($className);
        $context = new Context(['reflector' => $reflector]);

        return match ($method) {
            'get' => new OA\Get(['_context' => $context]),
            'post' => new OA\Post(['_context' => $context]),
            'put' => new OA\Put(['_context' => $context]),
            'delete' => new OA\Delete(['_context' => $context]),
        };
    }

    private function process(OA\Operation $operation): void
    {
        $analysis = new Analysis([$operation], new Context());
        ($this->processor)($analysis);
    }
}

// Test fixtures

#[Get(path: '/items/{id}', name: 'api_get_item')]
class ActionWithGet
{
    public function __invoke(): \stdClass
    {
        return new \stdClass();
    }
}

#[Delete(path: '/items/{id}', name: 'api_delete_item')]
class ActionWithDelete
{
    public function __invoke(): void
    {
    }
}

#[Post(path: '/items', name: 'api_create_item')]
class ActionWithPost
{
    public function __invoke(): \stdClass
    {
        return new \stdClass();
    }
}

#[Get(path: '/items', name: 'api_get_items')]
class ActionWithPaginator
{
    /**
     * @return Paginator<\stdClass>
     */
    public function __invoke(): Paginator
    {
        return new SelectablePaginator(new InMemoryCollection([]), 1, 20);
    }
}

#[Get(path: '/items', name: 'api_get_json')]
class ActionWithJsonResponse
{
    public function __invoke(): JsonResponse
    {
        return new JsonResponse();
    }
}

#[Put(path: '/items/{id}', name: 'api_replace_item')]
class ActionWithGetOrCreate
{
    /**
     * @return GetOrCreateResource<\stdClass>
     */
    public function __invoke(): GetOrCreateResource
    {
        return GetOrCreateResource::existing(new \stdClass());
    }
}

#[Put(path: '/items/{id}', name: 'api_void_put_item')]
class ActionWithVoidPut
{
    public function __invoke(): void
    {
    }
}

#[Delete(path: '/items/{id}', name: 'api_void_explicit_status', statusCode: 202)]
class ActionWithVoidAndExplicitStatusCode
{
    public function __invoke(): void
    {
    }
}

class ActionWithoutRoute
{
    public function __invoke(): void
    {
    }
}
