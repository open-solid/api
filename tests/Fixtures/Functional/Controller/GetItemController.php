<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\Functional\Controller;

use OpenSolid\Api\Routing\Attribute\Get;
use OpenSolid\Api\Tests\Fixtures\Functional\Model\ItemView;

#[Get(
    path: '/items/{id}',
    name: 'func_get_item',
)]
final readonly class GetItemController
{
    public function __invoke(string $id): ItemView
    {
        return new ItemView(id: $id, name: 'Test Item', price: 1000);
    }
}
