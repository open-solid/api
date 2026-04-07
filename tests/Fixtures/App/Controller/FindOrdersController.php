<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App\Controller;

use OpenSolid\Api\Routing\Attribute\GetCollection;
use OpenSolid\Api\Tests\Fixtures\App\Model\FindOrdersQuery;
use OpenSolid\Api\Tests\Fixtures\App\Model\OrderView;
use OpenSolid\Core\Domain\Repository\Paginator;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;

#[GetCollection(
    path: '/orders',
    name: 'api_find_orders',
    description: 'Find Orders',
    summary: 'Retrieves a paginated collection of order resources.',
    tags: ['Order'],
)]
final readonly class FindOrdersController
{
    /**
     * @return Paginator<OrderView>
     */
    public function __invoke(#[MapQueryString] ?FindOrdersQuery $query = null): Paginator
    {
        throw new \LogicException('Not implemented.');
    }
}
