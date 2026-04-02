<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App\Model;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema]
final class CreateProductPayload
{
    #[Assert\Uuid]
    #[OA\Property(description: 'The product ID', example: '019d0121-5df2-77df-be75-8933613d53ab')]
    public ?string $id = null;

    #[Assert\Length(min: 3, max: 255)]
    #[OA\Property(description: 'The product name', maxLength: 255, minLength: 3)]
    public string $name;

    #[Assert\Positive]
    #[OA\Property(description: 'The product price (in cents)', example: 100)]
    public int $price;

    #[Assert\Choice(choices: ['USD', 'EUR', 'GBP'])]
    #[OA\Property(description: 'The currency of the price', example: 'USD')]
    public string $currency = 'USD';
}
