<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\OpenApi;

use OpenSolid\Api\OpenApi\OpenApiGenerator;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\AbstractLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OpenApiValidationTest extends KernelTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        restore_exception_handler();
    }

    #[Test]
    public function generationProducesNoWarningsOrErrors(): void
    {
        $generator = self::getContainer()->get(OpenApiGenerator::class);
        $openApi = $generator->generate();

        $logs = [];
        $logger = new class($logs) extends AbstractLogger {
            public function __construct(private array &$logs)
            {
            }

            public function log($level, \Stringable|string $message, array $context = []): void
            {
                $this->logs[] = ['level' => (string) $level, 'message' => (string) $message];
            }
        };

        $openApi->_context->logger = $logger;
        $openApi->validate();

        $issues = array_filter($logs, static fn (array $log): bool => in_array($log['level'], ['warning', 'error'], true));

        self::assertSame([], $issues, 'OpenAPI validation should produce no warnings or errors');
    }
}
