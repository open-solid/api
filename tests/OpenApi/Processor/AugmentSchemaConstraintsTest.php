<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\OpenApi\Processor;

use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;
use OpenSolid\Api\OpenApi\Processor\AugmentSchemaConstraints;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints as Assert;

class AugmentSchemaConstraintsTest extends TestCase
{
    private AugmentSchemaConstraints $processor;

    protected function setUp(): void
    {
        $this->processor = new AugmentSchemaConstraints();
    }

    #[Test]
    public function itInfersUuidFormat(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\UuidProperty::class, 'value');

        self::assertSame('uuid', $property->format);
    }

    #[Test]
    public function itInfersUlidFormat(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\UlidProperty::class, 'value');

        self::assertSame('ulid', $property->format);
    }

    #[Test]
    public function itInfersEmailFormat(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\EmailProperty::class, 'value');

        self::assertSame('email', $property->format);
    }

    #[Test]
    public function itInfersUriFormat(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\UrlProperty::class, 'value');

        self::assertSame('uri', $property->format);
    }

    #[Test]
    public function itInfersHostnameFormat(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\HostnameProperty::class, 'value');

        self::assertSame('hostname', $property->format);
    }

    #[Test]
    public function itInfersIpv4Format(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\Ipv4Property::class, 'value');

        self::assertSame('ipv4', $property->format);
    }

    #[Test]
    public function itInfersIpv6Format(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\Ipv6Property::class, 'value');

        self::assertSame('ipv6', $property->format);
    }

    #[Test]
    public function itInfersDateFormat(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\DateProperty::class, 'value');

        self::assertSame('date', $property->format);
    }

    #[Test]
    public function itInfersDateTimeFormat(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\DateTimeProperty::class, 'value');

        self::assertSame('date-time', $property->format);
    }

    #[Test]
    public function itInfersTimeFormat(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\TimeProperty::class, 'value');

        self::assertSame('time', $property->format);
    }

    #[Test]
    public function itInfersMinLengthAndMaxLength(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\LengthProperty::class, 'value');

        self::assertSame(3, $property->minLength);
        self::assertSame(255, $property->maxLength);
    }

    #[Test]
    public function itInfersMinimumAndMaximumFromRange(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\RangeProperty::class, 'value');

        self::assertSame(1, $property->minimum);
        self::assertSame(100, $property->maximum);
    }

    #[Test]
    public function itInfersExclusiveMinimumFromGreaterThan(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\GreaterThanProperty::class, 'value');

        self::assertSame(5, $property->exclusiveMinimum);
    }

    #[Test]
    public function itInfersMinimumFromGreaterThanOrEqual(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\GreaterThanOrEqualProperty::class, 'value');

        self::assertSame(5, $property->minimum);
    }

    #[Test]
    public function itInfersExclusiveMaximumFromLessThan(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\LessThanProperty::class, 'value');

        self::assertSame(10, $property->exclusiveMaximum);
    }

    #[Test]
    public function itInfersMaximumFromLessThanOrEqual(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\LessThanOrEqualProperty::class, 'value');

        self::assertSame(10, $property->maximum);
    }

    #[Test]
    public function itInfersExclusiveMinimumFromPositive(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\PositiveProperty::class, 'value');

        self::assertSame(0, $property->exclusiveMinimum);
    }

    #[Test]
    public function itInfersMinimumFromPositiveOrZero(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\PositiveOrZeroProperty::class, 'value');

        self::assertSame(0, $property->minimum);
    }

    #[Test]
    public function itInfersExclusiveMaximumFromNegative(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\NegativeProperty::class, 'value');

        self::assertSame(0, $property->exclusiveMaximum);
    }

    #[Test]
    public function itInfersMaximumFromNegativeOrZero(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\NegativeOrZeroProperty::class, 'value');

        self::assertSame(0, $property->maximum);
    }

    #[Test]
    public function itInfersPatternFromRegex(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\RegexProperty::class, 'value');

        self::assertSame('^[a-z]+$', $property->pattern);
    }

    #[Test]
    public function itSkipsRegexWhenMatchIsFalse(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\RegexNonMatchProperty::class, 'value');

        self::assertTrue(Generator::isDefault($property->pattern));
    }

    #[Test]
    public function itInfersEnumFromChoice(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\ChoiceProperty::class, 'value');

        self::assertSame(['foo', 'bar', 'baz'], $property->enum);
    }

    #[Test]
    public function itSkipsChoiceWhenMultiple(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\ChoiceMultipleProperty::class, 'values');

        self::assertTrue(Generator::isDefault($property->enum));
    }

    #[Test]
    public function itInfersMinItemsAndMaxItemsFromCount(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\CountProperty::class, 'values');

        self::assertSame(1, $property->minItems);
        self::assertSame(10, $property->maxItems);
    }

    #[Test]
    public function itDoesNotOverwriteExplicitFormat(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\ExplicitFormatProperty::class, 'value');

        self::assertSame('custom-format', $property->format);
    }

    #[Test]
    public function itDoesNotOverwriteExplicitMinLength(): void
    {
        $property = $this->processProperty(AugmentSchemaConstraintsFixtures\ExplicitMinLengthProperty::class, 'value');

        self::assertSame(10, $property->minLength);
        self::assertSame(255, $property->maxLength);
    }

    #[Test]
    public function itSkipsPropertyWithoutReflector(): void
    {
        $property = new OA\Property(['_context' => new Context()]);
        $schema = new OA\Schema(['_context' => new Context()]);
        $schema->properties = [$property];

        $analysis = $this->createAnalysis($schema);
        ($this->processor)($analysis);

        self::assertTrue(Generator::isDefault($property->format));
    }

    #[Test]
    public function itHandlesPromotedConstructorProperties(): void
    {
        $property = $this->processProperty(
            AugmentSchemaConstraintsFixtures\PromotedProperty::class,
            'email',
            useConstructorParam: true,
        );

        self::assertSame('email', $property->format);
    }

    private function processProperty(string $class, string $propertyName, bool $useConstructorParam = false): OA\Property
    {
        if ($useConstructorParam) {
            $reflector = new \ReflectionParameter([$class, '__construct'], $propertyName);
            $attrReflector = $reflector;
        } else {
            $reflector = new \ReflectionProperty($class, $propertyName);
            $attrReflector = $reflector;
        }

        // Use the OA\Property attribute from the fixture if present
        $oaAttributes = $attrReflector->getAttributes(\OpenApi\Attributes\Property::class);
        if ($oaAttributes !== []) {
            $property = $oaAttributes[0]->newInstance();
        } else {
            $property = new OA\Property(['_context' => new Context(['reflector' => $reflector])]);
        }
        $property->_context = new Context(['reflector' => $reflector]);

        $schema = new OA\Schema(['_context' => new Context()]);
        $schema->properties = [$property];

        $analysis = $this->createAnalysis($schema);
        ($this->processor)($analysis);

        return $property;
    }

    private function createAnalysis(OA\Schema $schema): \OpenApi\Analysis
    {
        $analysis = new \OpenApi\Analysis([], new Context());
        $analysis->addAnnotation($schema, new Context());

        return $analysis;
    }
}

// Fixture classes

namespace OpenSolid\Api\Tests\OpenApi\Processor\AugmentSchemaConstraintsFixtures;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class UuidProperty
{
    #[Assert\Uuid]
    public string $value;
}

class UlidProperty
{
    #[Assert\Ulid]
    public string $value;
}

class EmailProperty
{
    #[Assert\Email]
    public string $value;
}

class UrlProperty
{
    #[Assert\Url]
    public string $value;
}

class HostnameProperty
{
    #[Assert\Hostname]
    public string $value;
}

class Ipv4Property
{
    #[Assert\Ip(version: '4')]
    public string $value;
}

class Ipv6Property
{
    #[Assert\Ip(version: '6')]
    public string $value;
}

class DateProperty
{
    #[Assert\Date]
    public string $value;
}

class DateTimeProperty
{
    #[Assert\DateTime]
    public string $value;
}

class TimeProperty
{
    #[Assert\Time]
    public string $value;
}

class LengthProperty
{
    #[Assert\Length(min: 3, max: 255)]
    public string $value;
}

class RangeProperty
{
    #[Assert\Range(min: 1, max: 100)]
    public int $value;
}

class GreaterThanProperty
{
    #[Assert\GreaterThan(5)]
    public int $value;
}

class GreaterThanOrEqualProperty
{
    #[Assert\GreaterThanOrEqual(5)]
    public int $value;
}

class LessThanProperty
{
    #[Assert\LessThan(10)]
    public int $value;
}

class LessThanOrEqualProperty
{
    #[Assert\LessThanOrEqual(10)]
    public int $value;
}

class PositiveProperty
{
    #[Assert\Positive]
    public int $value;
}

class PositiveOrZeroProperty
{
    #[Assert\PositiveOrZero]
    public int $value;
}

class NegativeProperty
{
    #[Assert\Negative]
    public int $value;
}

class NegativeOrZeroProperty
{
    #[Assert\NegativeOrZero]
    public int $value;
}

class RegexProperty
{
    #[Assert\Regex('/^[a-z]+$/')]
    public string $value;
}

class RegexNonMatchProperty
{
    #[Assert\Regex(pattern: '/^[a-z]+$/', match: false)]
    public string $value;
}

class ChoiceProperty
{
    #[Assert\Choice(choices: ['foo', 'bar', 'baz'])]
    public string $value;
}

class ChoiceMultipleProperty
{
    #[Assert\Choice(choices: ['foo', 'bar', 'baz'], multiple: true)]
    public array $values;
}

class CountProperty
{
    #[Assert\Count(min: 1, max: 10)]
    public array $values;
}

class ExplicitFormatProperty
{
    #[Assert\Uuid]
    #[OA\Property(format: 'custom-format')]
    public string $value;
}

class ExplicitMinLengthProperty
{
    #[Assert\Length(min: 3, max: 255)]
    #[OA\Property(minLength: 10)]
    public string $value;
}

class PromotedProperty
{
    public function __construct(
        #[Assert\Email]
        public string $email,
    ) {
    }
}
