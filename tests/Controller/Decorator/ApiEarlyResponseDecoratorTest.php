<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Controller\Decorator;

use OpenSolid\Api\Controller\Decorator\ApiEarlyResponseDecorator;
use OpenSolid\CallableInvoker\CallableMetadata;
use OpenSolid\CallableInvoker\Decorator\CallableClosure;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeResolver\TypeResolverInterface;

class ApiEarlyResponseDecoratorTest extends TestCase
{
    #[Test]
    public function itReturnsJsonResponseAsIs(): void
    {
        $decorator = new ApiEarlyResponseDecorator($this->createStub(TypeResolverInterface::class));
        $jsonResponse = new JsonResponse(['data' => 'test']);
        $callable = new CallableClosure(static fn () => $jsonResponse, []);
        $metadata = $this->createMetadata();

        $result = $decorator->decorate($callable, $metadata);

        self::assertSame($jsonResponse, $result);
    }

    #[Test]
    public function itReturnsNoContentResponseForNull(): void
    {
        $decorator = new ApiEarlyResponseDecorator($this->createStub(TypeResolverInterface::class));
        $callable = new CallableClosure(static fn () => null, []);
        $metadata = $this->createMetadata();

        $result = $decorator->decorate($callable, $metadata);

        self::assertInstanceOf(JsonResponse::class, $result);
        self::assertSame(204, $result->getStatusCode());
    }

    #[Test]
    public function itResolvesReturnTypeAndPassesResponseThrough(): void
    {
        $typeResolver = $this->createMock(TypeResolverInterface::class);
        $decorator = new ApiEarlyResponseDecorator($typeResolver);
        $response = new \stdClass();
        $callable = new CallableClosure(static fn () => $response, []);
        $metadata = $this->createMetadata();

        $typeResolver->expects(self::once())
            ->method('resolve')
            ->with($metadata->function)
            ->willReturn(Type::object(\stdClass::class));

        $result = $decorator->decorate($callable, $metadata);

        self::assertSame($response, $result);
        self::assertEquals(Type::object(\stdClass::class), $metadata->getAttribute('return_type'));
    }

    private function createMetadata(): CallableMetadata
    {
        return new CallableMetadata(
            new \ReflectionFunction(static fn () => null),
            [],
            [],
        );
    }
}
