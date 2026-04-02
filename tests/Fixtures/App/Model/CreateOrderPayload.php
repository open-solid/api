<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App\Model;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema]
final class CreateOrderPayload
{
    #[Assert\NotBlank]
    #[OA\Property(description: 'The external system ID')]
    public string $externalId;

    #[Assert\Positive]
    #[OA\Property(description: 'The total amount in cents', example: 5000)]
    public int $total;

    #[Assert\Choice(choices: ['USD', 'EUR', 'GBP'])]
    #[OA\Property(description: 'The currency code', example: 'USD')]
    public string $currency = 'USD';
}
