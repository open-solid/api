<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App\Controller;

use OpenSolid\Api\Routing\Attribute\GetCollection;
use OpenSolid\Api\Tests\Fixtures\App\Model\FindOrdersByProductQuery;
use OpenSolid\Api\Tests\Fixtures\App\Model\OrderView;
use OpenSolid\Core\Domain\Repository\Paginator;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;

#[GetCollection(
    path: '/orders/by-product',
    name: 'api_find_orders_by_product',
    description: 'Find Orders by Product',
    summary: 'Retrieves orders filtered by product.',
    tags: ['Order'],
)]
final readonly class FindOrdersByProductController
{
    /**
     * @return Paginator<OrderView>
     */
    public function __invoke(#[MapQueryString] ?FindOrdersByProductQuery $query = null): Paginator
    {
        throw new \LogicException('Not implemented.');
    }
}
