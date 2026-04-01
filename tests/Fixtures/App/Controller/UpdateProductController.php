<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App\Controller;

use OpenApi\Attributes\PathParameter;
use OpenSolid\Api\Routing\Attribute\Patch;
use OpenSolid\Api\Tests\Fixtures\App\Model\ProductId;
use OpenSolid\Api\Tests\Fixtures\App\Model\ProductView;
use OpenSolid\Api\Tests\Fixtures\App\Model\UpdateProductPayload;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[Patch(
    path: '/products/{id}',
    name: 'api_update_product',
    description: 'Update a Product',
    summary: 'Partially updates an existing product by its unique identifier.',
    tags: ['Product'],
)]
final readonly class UpdateProductController
{
    public function __invoke(#[PathParameter] ProductId $id, #[MapRequestPayload] UpdateProductPayload $payload): ProductView
    {
        throw new \LogicException('Not implemented.');
    }
}
