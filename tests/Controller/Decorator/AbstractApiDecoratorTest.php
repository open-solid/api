<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Controller\Decorator;

use OpenSolid\CallableInvoker\CallableMetadata;
use OpenSolid\CallableInvoker\Decorator\CallableClosure;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class AbstractApiDecoratorTest extends TestCase
{
    #[Test]
    public function itSupportsMainApiJsonRequests(): void
    {
        $decorator = new ConcreteApiDecorator();
        $metadata = $this->createMetadata(isMainRequest: true, format: 'json', isApiController: true);

        self::assertTrue($decorator->supports($metadata));
    }

    #[Test]
    public function itDoesNotSupportSubRequests(): void
    {
        $decorator = new ConcreteApiDecorator();
        $metadata = $this->createMetadata(isMainRequest: false, format: 'json', isApiController: true);

        self::assertFalse($decorator->supports($metadata));
    }

    #[Test]
    public function itDoesNotSupportNonJsonRequests(): void
    {
        $decorator = new ConcreteApiDecorator();
        $metadata = $this->createMetadata(isMainRequest: true, format: 'html', isApiController: true);

        self::assertFalse($decorator->supports($metadata));
    }

    #[Test]
    public function itDoesNotSupportNonApiControllerRequests(): void
    {
        $decorator = new ConcreteApiDecorator();
        $metadata = $this->createMetadata(isMainRequest: true, format: 'json', isApiController: false);

        self::assertFalse($decorator->supports($metadata));
    }

    #[Test]
    public function itDoesNotSupportNonControllerArgumentsEvents(): void
    {
        $decorator = new ConcreteApiDecorator();
        $metadata = new CallableMetadata(
            new \ReflectionFunction(static fn () => null),
            ['event' => new \stdClass()],
            [],
        );

        self::assertFalse($decorator->supports($metadata));
    }

    private function createMetadata(bool $isMainRequest, string $format, bool $isApiController): CallableMetadata
    {
        $request = new Request();
        $request->setRequestFormat($format);
        if ($isApiController) {
            $request->attributes->set('_api_controller', true);
        }

        $kernel = $this->createStub(HttpKernelInterface::class);
        $event = new ControllerArgumentsEvent(
            $kernel,
            static fn () => null,
            [],
            $request,
            $isMainRequest ? HttpKernelInterface::MAIN_REQUEST : HttpKernelInterface::SUB_REQUEST,
        );

        return new CallableMetadata(
            new \ReflectionFunction(static fn () => null),
            ['event' => $event, 'request' => $request],
            [],
        );
    }
}
