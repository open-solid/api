<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App\Model;

use OpenApi\Attributes as OA;

#[OA\Schema]
final class UpdateProductPayload
{
    #[OA\Property(description: 'The product name', maxLength: 255, minLength: 3)]
    public ?string $name = null;

    #[OA\Property(description: 'The product price amount (in cents)', example: 100)]
    public ?int $amount = null;

    #[OA\Property(description: 'The product price currency code', example: 'USD')]
    public ?string $currency = null;
}
