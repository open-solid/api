<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App\Controller;

use OpenSolid\Api\Routing\Attribute\GetCollection;
use OpenSolid\Api\Tests\Fixtures\App\Model\FindProductsQuery;
use OpenSolid\Api\Tests\Fixtures\App\Model\ProductView;
use OpenSolid\Core\Domain\Repository\Paginator;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;

#[GetCollection(
    path: '/products',
    name: 'api_find_products',
    description: 'Find Products',
    summary: 'Retrieves a paginated collection of product resources.',
    tags: ['Product'],
)]
final readonly class FindProductsController
{
    /**
     * @return Paginator<ProductView>
     */
    public function __invoke(#[MapQueryString] ?FindProductsQuery $query = null): Paginator
    {
        throw new \LogicException('Not implemented.');
    }
}
