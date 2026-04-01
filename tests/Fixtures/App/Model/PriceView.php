<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App\Model;

use OpenApi\Attributes as OA;

#[OA\Schema]
final readonly class PriceView
{
    public function __construct(
        #[OA\Property(description: 'The amount of the price', example: 100)]
        public int $amount,
        #[OA\Property(description: 'The currency of the price', example: 'USD')]
        public string $currency,
    ) {
    }
}
