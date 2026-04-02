<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Controller\Decorator;

use OpenSolid\Api\Controller\Decorator\ApiResponseDecorator;
use OpenSolid\CallableInvoker\CallableMetadata;
use OpenSolid\CallableInvoker\Decorator\CallableClosure;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApiResponseDecoratorTest extends TestCase
{
    #[Test]
    public function itReturnsJsonResponseAsIs(): void
    {
        $decorator = new ApiResponseDecorator();
        $jsonResponse = new JsonResponse(['data' => 'test']);
        $callable = new CallableClosure(static fn () => $jsonResponse, []);
        $metadata = $this->createMetadata();

        $result = $decorator->decorate($callable, $metadata);

        self::assertSame($jsonResponse, $result);
    }

    private function createMetadata(): CallableMetadata
    {
        $request = new Request();
        $request->attributes->set('_api_status_code', 200);

        return new CallableMetadata(
            new \ReflectionFunction(static fn () => null),
            ['request' => $request],
            [],
        );
    }
}
