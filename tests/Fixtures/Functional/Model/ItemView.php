<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\Functional\Model;

use Symfony\Component\JsonStreamer\Attribute\JsonStreamable;

#[JsonStreamable]
final readonly class ItemView
{
    public function __construct(
        public string $id,
        public string $name,
        public int $price,
    ) {
    }
}
