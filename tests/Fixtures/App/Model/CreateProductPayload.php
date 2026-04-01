<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App\Model;

use OpenApi\Attributes as OA;

#[OA\Schema]
final readonly class CreateProductPayload
{
    public function __construct(
        #[OA\Property(description: 'The product ID', format: 'uuid', example: '019d0121-5df2-77df-be75-8933613d53ab')]
        public ?string $id = null,
        #[OA\Property(description: 'The product name', maxLength: 255, minLength: 3)]
        public string $name,
        #[OA\Property(description: 'The product price (in cents)', example: 100)]
        public int $price,
        #[OA\Property(description: 'The currency of the price', example: 'USD')]
        public string $currency = 'USD',
    ) {
    }
}
