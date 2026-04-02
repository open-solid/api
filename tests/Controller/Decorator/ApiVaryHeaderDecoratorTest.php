<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Controller\Decorator;

use OpenSolid\Api\Controller\Decorator\ApiVaryHeaderDecorator;
use OpenSolid\CallableInvoker\CallableMetadata;
use OpenSolid\CallableInvoker\Decorator\CallableClosure;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApiVaryHeaderDecoratorTest extends TestCase
{
    #[Test]
    public function itSetsVaryHeadersOnJsonResponse(): void
    {
        $decorator = new ApiVaryHeaderDecorator(['Content-Type', 'Authorization', 'Origin']);
        $response = new JsonResponse(['data' => 'test']);
        $callable = new CallableClosure(static fn () => $response, []);
        $metadata = $this->createMetadata();

        $result = $decorator->decorate($callable, $metadata);

        self::assertSame($response, $result);
        self::assertSame(['Content-Type', 'Authorization', 'Origin'], $result->getVary());
    }

    #[Test]
    public function itSetsVaryHeadersOnStreamedResponse(): void
    {
        $decorator = new ApiVaryHeaderDecorator(['Content-Type', 'Authorization', 'Origin']);
        $response = new StreamedResponse(static function () { echo '{}'; });
        $callable = new CallableClosure(static fn () => $response, []);
        $metadata = $this->createMetadata();

        $result = $decorator->decorate($callable, $metadata);

        self::assertSame($response, $result);
        self::assertSame(['Content-Type', 'Authorization', 'Origin'], $result->getVary());
    }

    #[Test]
    public function itDoesNotReplaceExistingVaryHeaders(): void
    {
        $decorator = new ApiVaryHeaderDecorator(['Origin']);
        $response = new JsonResponse();
        $response->setVary(['Accept-Encoding']);
        $callable = new CallableClosure(static fn () => $response, []);
        $metadata = $this->createMetadata();

        $result = $decorator->decorate($callable, $metadata);

        self::assertSame(['Accept-Encoding', 'Origin'], $result->getVary());
    }

    #[Test]
    public function itPassesThroughNonResponseValues(): void
    {
        $decorator = new ApiVaryHeaderDecorator(['Content-Type']);
        $object = new \stdClass();
        $callable = new CallableClosure(static fn () => $object, []);
        $metadata = $this->createMetadata();

        $result = $decorator->decorate($callable, $metadata);

        self::assertSame($object, $result);
    }

    #[Test]
    public function itSkipsWhenHeadersAreEmpty(): void
    {
        $decorator = new ApiVaryHeaderDecorator([]);
        $response = new JsonResponse();
        $callable = new CallableClosure(static fn () => $response, []);
        $metadata = $this->createMetadata();

        $result = $decorator->decorate($callable, $metadata);

        self::assertSame($response, $result);
        self::assertSame([], $result->getVary());
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
