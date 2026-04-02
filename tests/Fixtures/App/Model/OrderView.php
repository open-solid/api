<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App\Model;

use OpenApi\Attributes as OA;

#[OA\Schema]
final readonly class OrderView
{
    public function __construct(
        #[OA\Property(description: 'The order ID', format: 'uuid', example: '019d0121-5df2-77df-be75-8933613d53ab')]
        public string $id,

        #[OA\Property(description: 'The external system ID')]
        public string $externalId,

        #[OA\Property(description: 'The order status', example: 'pending')]
        public string $status,

        #[OA\Property(description: 'The total amount in cents', example: 5000)]
        public int $total,

        #[OA\Property(description: 'The currency code', example: 'USD')]
        public string $currency,
    ) {
    }
}
