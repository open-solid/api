<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\OpenApi\Processor;

use OpenApi\Analysis;
use OpenApi\Attributes as OAT;
use OpenApi\Context;
use OpenSolid\Api\OpenApi\Processor\RemoveScannedQueryParameters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RemoveScannedQueryParametersTest extends TestCase
{
    private RemoveScannedQueryParameters $processor;

    protected function setUp(): void
    {
        $this->processor = new RemoveScannedQueryParameters();
    }

    #[Test]
    public function itRemovesScannedQueryParametersWithReflectionPropertyContext(): void
    {
        // Simulate swagger-php scanning: QueryParameter annotations with nested=false
        // (exactly as AttributeAnnotationFactory does for property-level attributes)
        $scannedAnnotations = [];
        $class = new \ReflectionClass(ScannedParams::class);
        foreach ($class->getProperties() as $property) {
            $attrs = $property->getAttributes(OAT\QueryParameter::class, \ReflectionAttribute::IS_INSTANCEOF);
            if ($attrs !== []) {
                $scanned = $attrs[0]->newInstance();
                $scanned->_context = new Context([
                    'nested' => false,
                    'property' => $property->getName(),
                    'reflector' => $property,
                ]);
                $scannedAnnotations[] = $scanned;
            }
        }

        $analysis = new Analysis($scannedAnnotations, new Context());

        self::assertNotEmpty($analysis->getAnnotationsOfType(OAT\QueryParameter::class));

        ($this->processor)($analysis);

        self::assertEmpty($analysis->getAnnotationsOfType(OAT\QueryParameter::class));
    }

    #[Test]
    public function itKeepsNestedQueryParameters(): void
    {
        // Simulate a QueryParameter created by AugmentQueryParameterSets (nested=true)
        $property = (new \ReflectionClass(ScannedParams::class))->getProperty('name');
        $queryParam = new OAT\QueryParameter(description: 'Filter by name');
        $queryParam->_context = new Context([
            'nested' => true,
            'property' => 'name',
            'reflector' => $property,
        ]);

        $analysis = new Analysis([$queryParam], new Context());

        ($this->processor)($analysis);

        self::assertCount(1, $analysis->getAnnotationsOfType(OAT\QueryParameter::class));
    }

    #[Test]
    public function itRemovesDuplicateScannedParametersAcrossClasses(): void
    {
        // Two different classes both with an 'externalId' parameter — the real-world scenario
        $scannedAnnotations = [];
        foreach ([ScannedParams::class, OtherScannedParams::class] as $className) {
            $class = new \ReflectionClass($className);
            foreach ($class->getProperties() as $property) {
                $attrs = $property->getAttributes(OAT\QueryParameter::class, \ReflectionAttribute::IS_INSTANCEOF);
                if ($attrs !== []) {
                    $scanned = $attrs[0]->newInstance();
                    $scanned->_context = new Context([
                        'nested' => false,
                        'property' => $property->getName(),
                        'reflector' => $property,
                    ]);
                    $scannedAnnotations[] = $scanned;
                }
            }
        }

        $analysis = new Analysis($scannedAnnotations, new Context());

        // Both classes contribute annotations, including duplicate 'externalId'
        self::assertGreaterThan(2, count($analysis->getAnnotationsOfType(OAT\QueryParameter::class)));

        ($this->processor)($analysis);

        self::assertEmpty($analysis->getAnnotationsOfType(OAT\QueryParameter::class));
    }
}

// Test fixtures

class ScannedParams
{
    #[OAT\QueryParameter(description: 'Filter by name')]
    public ?string $name = null;

    #[OAT\QueryParameter(description: 'Filter by external ID')]
    public ?string $externalId = null;
}

class OtherScannedParams
{
    #[OAT\QueryParameter(description: 'Filter by external ID')]
    public ?string $externalId = null;

    #[OAT\QueryParameter(description: 'Filter by status')]
    public ?string $status = null;
}
