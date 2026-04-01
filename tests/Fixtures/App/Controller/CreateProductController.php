<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App\Controller;

use OpenSolid\Api\Routing\Attribute\Post;
use OpenSolid\Api\Tests\Fixtures\App\Model\CreateProductPayload;
use OpenSolid\Api\Tests\Fixtures\App\Model\ProductView;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[Post(
    path: '/products',
    name: 'api_create_product',
    description: 'Create a Product',
    summary: 'Creates a new product resource.',
    tags: ['Product'],
)]
final readonly class CreateProductController
{
    public function __invoke(#[MapRequestPayload] CreateProductPayload $payload): ProductView
    {
        throw new \LogicException('Not implemented.');
    }
}
