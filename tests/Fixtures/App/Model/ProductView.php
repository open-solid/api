<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App\Model;

use OpenApi\Attributes as OA;

#[OA\Schema]
final readonly class ProductView
{
    public function __construct(
        #[OA\Property(description: 'The unique identifier of the product', format: 'uuid', example: '019d0121-5df2-77df-be75-8933613d53ab')]
        public string $id,
        #[OA\Property(description: 'The name of the product')]
        public string $name,
        #[OA\Property(description: 'The price of the product')]
        public PriceView $price,
    ) {
    }
}
