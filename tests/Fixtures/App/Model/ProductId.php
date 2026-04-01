<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App\Model;

use Symfony\Component\Uid\Uuid;

final readonly class ProductId
{
    public function __construct(
        public Uuid $value,
    ) {
    }

    public static function from(string $value): self
    {
        return new self(Uuid::fromString($value));
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
