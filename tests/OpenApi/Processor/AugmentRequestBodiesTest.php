<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\OpenApi\Processor;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAT;
use OpenApi\Context;
use OpenApi\Generator;
use OpenSolid\Api\OpenApi\Processor\AugmentRequestBodies;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class AugmentRequestBodiesTest extends TestCase
{
    private AugmentRequestBodies $processor;

    protected function setUp(): void
    {
        $this->processor = new AugmentRequestBodies('application/json');
    }

    #[Test]
    public function itCreatesRequestBodyFromMapRequestPayload(): void
    {
        $operation = $this->createOperation(ActionWithMapRequestPayload::class);

        $this->process($operation);

        self::assertInstanceOf(OA\RequestBody::class, $operation->requestBody);
        self::assertTrue($operation->requestBody->required);
        self::assertIsArray($operation->requestBody->content);
        self::assertSame('application/json', $operation->requestBody->content[0]->mediaType);
        self::assertSame(AugmentRequestBodiesPayload::class, $operation->requestBody->content[0]->schema->ref);
    }

    #[Test]
    public function itSetsRequiredFalseForNullablePayload(): void
    {
        $operation = $this->createOperation(ActionWithNullablePayload::class);

        $this->process($operation);

        self::assertInstanceOf(OA\RequestBody::class, $operation->requestBody);
        self::assertFalse($operation->requestBody->required);
    }

    #[Test]
    public function itFillsContentOnExplicitRequestBody(): void
    {
        $operation = $this->createOperation(ActionWithBothAttributes::class);

        // Simulate MergeMethodAnnotationsIntoOperations having merged the OA\RequestBody
        $requestBody = new OA\RequestBody([
            'description' => 'Custom description',
            '_context' => $operation->_context,
        ]);
        $operation->requestBody = $requestBody;

        $this->process($operation);

        self::assertSame('Custom description', $operation->requestBody->description);
        self::assertIsArray($operation->requestBody->content);
        self::assertSame('application/json', $operation->requestBody->content[0]->mediaType);
        self::assertSame(AugmentRequestBodiesPayload::class, $operation->requestBody->content[0]->schema->ref);
        self::assertTrue($operation->requestBody->required);
    }

    #[Test]
    public function itDoesNotOverrideExplicitContent(): void
    {
        $operation = $this->createOperation(ActionWithBothAttributes::class);

        $context = $operation->_context;
        $existingMedia = new OA\MediaType([
            'mediaType' => 'text/xml',
            '_context' => $context,
        ]);
        $requestBody = new OA\RequestBody([
            'content' => [$existingMedia],
            'required' => false,
            '_context' => $context,
        ]);
        $operation->requestBody = $requestBody;

        $this->process($operation);

        self::assertSame('text/xml', $operation->requestBody->content[0]->mediaType);
        self::assertFalse($operation->requestBody->required);
    }

    #[Test]
    public function itSkipsOperationWithoutMapRequestPayload(): void
    {
        $operation = $this->createOperation(ActionWithoutPayload::class);

        $this->process($operation);

        self::assertTrue(Generator::isDefault($operation->requestBody));
    }

    #[Test]
    public function itSkipsOperationWithBuiltinParameterType(): void
    {
        $operation = $this->createOperation(ActionWithBuiltinPayload::class);

        $this->process($operation);

        self::assertTrue(Generator::isDefault($operation->requestBody));
    }

    #[Test]
    public function itSkipsOperationWithExistingRequestBody(): void
    {
        $operation = $this->createOperation(ActionWithMapRequestPayload::class);

        // Already has a fully-configured requestBody
        $context = $operation->_context;
        $existingMedia = new OA\MediaType([
            'mediaType' => 'application/xml',
            '_context' => $context,
        ]);
        $operation->requestBody = new OA\RequestBody([
            'content' => [$existingMedia],
            'required' => false,
            '_context' => $context,
        ]);

        $this->process($operation);

        // Nothing overridden
        self::assertSame('application/xml', $operation->requestBody->content[0]->mediaType);
        self::assertFalse($operation->requestBody->required);
    }

    private function createOperation(string $className): OA\Post
    {
        $reflector = new \ReflectionClass($className);
        $context = new Context(['reflector' => $reflector]);

        return new OA\Post(['_context' => $context]);
    }

    private function process(OA\Operation $operation): void
    {
        $analysis = new Analysis([$operation], new Context());
        ($this->processor)($analysis);
    }
}

// Test fixtures

#[OAT\Schema]
class AugmentRequestBodiesPayload
{
    public string $name;
}

class ActionWithMapRequestPayload
{
    public function __invoke(#[MapRequestPayload] AugmentRequestBodiesPayload $payload): void
    {
    }
}

class ActionWithNullablePayload
{
    public function __invoke(#[MapRequestPayload] ?AugmentRequestBodiesPayload $payload): void
    {
    }
}

class ActionWithBothAttributes
{
    public function __invoke(
        #[OAT\RequestBody(description: 'Custom description')]
        #[MapRequestPayload]
        AugmentRequestBodiesPayload $payload,
    ): void {
    }
}

class ActionWithoutPayload
{
    public function __invoke(string $id): void
    {
    }
}

class ActionWithBuiltinPayload
{
    public function __invoke(#[MapRequestPayload] string $data): void
    {
    }
}
