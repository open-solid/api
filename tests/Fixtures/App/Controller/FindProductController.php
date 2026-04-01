<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App\Controller;

use OpenApi\Attributes\PathParameter;
use OpenSolid\Api\Routing\Attribute\Get;
use OpenSolid\Api\Tests\Fixtures\App\Model\ProductId;
use OpenSolid\Api\Tests\Fixtures\App\Model\ProductView;

#[Get(
    path: '/products/{id}',
    name: 'api_find_product',
    description: 'Find a Product',
    summary: 'Retrieves a single product by its unique identifier.',
    tags: ['Product'],
)]
final readonly class FindProductController
{
    public function __invoke(#[PathParameter] ProductId $id): ProductView
    {
        throw new \LogicException('Not implemented.');
    }
}
