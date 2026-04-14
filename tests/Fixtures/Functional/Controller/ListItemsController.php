<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\Functional\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use OpenSolid\Api\Routing\Attribute\GetCollection;
use OpenSolid\Api\Tests\Fixtures\Functional\Model\ItemView;
use OpenSolid\Core\Domain\Repository\Paginator;
use OpenSolid\Core\Domain\Repository\SelectablePaginator;

#[GetCollection(
    path: '/items',
    name: 'func_list_items',
)]
final readonly class ListItemsController
{
    /**
     * @return Paginator<ItemView>
     */
    public function __invoke(): Paginator
    {
        $items = new ArrayCollection([
            new ItemView('id-1', 'Item 1', 1000),
            new ItemView('id-2', 'Item 2', 2000),
        ]);

        return new SelectablePaginator($items, currentPage: 1, itemsPerPage: 20);
    }
}
