<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\OpenApi;

use OpenSolid\Api\OpenApi\OpenApiGenerator;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OpenApiSnapshotTest extends KernelTestCase
{
    private const string SNAPSHOT_PATH = __DIR__.'/expected_openapi.json';

    protected function tearDown(): void
    {
        parent::tearDown();
        restore_exception_handler();
    }

    #[Test]
    public function specMatchesSnapshot(): void
    {
        $generator = self::getContainer()->get(OpenApiGenerator::class);
        $actual = json_decode($generator->generate()->toJson(), true);
        $expected = json_decode(file_get_contents(self::SNAPSHOT_PATH), true);

        self::assertSame($expected, $actual, sprintf(
            "OpenAPI spec has changed. If this is intentional, update the snapshot:\n  cp openapi.json %s",
            self::SNAPSHOT_PATH,
        ));
    }
}
