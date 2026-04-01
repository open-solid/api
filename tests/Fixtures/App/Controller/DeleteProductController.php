<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App\Controller;

use OpenApi\Attributes\PathParameter;
use OpenSolid\Api\Routing\Attribute\Delete;
use OpenSolid\Api\Tests\Fixtures\App\Model\ProductId;

#[Delete(
    path: '/products/{id}',
    name: 'api_delete_product',
    description: 'Delete a Product',
    summary: 'Deletes a product by its unique identifier.',
    tags: ['Product'],
)]
final readonly class DeleteProductController
{
    public function __invoke(#[PathParameter] ProductId $id): void
    {
    }
}
