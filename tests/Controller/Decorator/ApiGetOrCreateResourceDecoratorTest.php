<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Controller\Decorator;

use OpenSolid\Api\Controller\Decorator\ApiGetOrCreateResourceDecorator;
use OpenSolid\Api\Controller\Model\ResponseOptions;
use OpenSolid\CallableInvoker\CallableMetadata;
use OpenSolid\CallableInvoker\Decorator\CallableClosure;
use OpenSolid\Core\Domain\Model\GetOrCreateResource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\TypeInfo\Type;

class ApiGetOrCreateResourceDecoratorTest extends TestCase
{
    private ApiGetOrCreateResourceDecorator $decorator;

    protected function setUp(): void
    {
        $this->decorator = new ApiGetOrCreateResourceDecorator();
    }

    #[Test]
    public function itPassesThroughNonGetOrCreateResponses(): void
    {
        $response = new \stdClass();
        $callable = new CallableClosure(static fn () => $response, []);
        $metadata = $this->createMetadata();

        $result = $this->decorator->decorate($callable, $metadata);

        self::assertSame($response, $result);
    }

    #[Test]
    public function itReturnsResponseOptionsWithStatus201ForCreatedResource(): void
    {
        $resource = new \stdClass();
        $callable = new CallableClosure(static fn () => GetOrCreateResource::created($resource), []);
        $metadata = $this->createMetadata();
        $metadata->setAttribute('return_type', Type::generic(
            Type::object(GetOrCreateResource::class),
            Type::object(\stdClass::class),
        ));

        $result = $this->decorator->decorate($callable, $metadata);

        self::assertInstanceOf(ResponseOptions::class, $result);
        self::assertSame($resource, $result->response);
        self::assertSame(201, $result->statusCode);
    }

    #[Test]
    public function itReturnsResponseOptionsWithStatus200ForExistingResource(): void
    {
        $resource = new \stdClass();
        $callable = new CallableClosure(static fn () => GetOrCreateResource::existing($resource), []);
        $metadata = $this->createMetadata();
        $metadata->setAttribute('return_type', Type::generic(
            Type::object(GetOrCreateResource::class),
            Type::object(\stdClass::class),
        ));

        $result = $this->decorator->decorate($callable, $metadata);

        self::assertInstanceOf(ResponseOptions::class, $result);
        self::assertSame($resource, $result->response);
        self::assertSame(200, $result->statusCode);
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
