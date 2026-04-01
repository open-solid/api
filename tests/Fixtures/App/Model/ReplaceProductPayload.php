<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App\Model;

use OpenApi\Attributes as OA;

#[OA\Schema]
final class ReplaceProductPayload
{
    #[OA\Property(description: 'The product name', maxLength: 255, minLength: 3)]
    public string $name;

    #[OA\Property(description: 'The product price amount (in cents)', example: 100)]
    public int $amount;

    #[OA\Property(description: 'The currency of the price', example: 'USD')]
    public string $currency = 'USD';
}
