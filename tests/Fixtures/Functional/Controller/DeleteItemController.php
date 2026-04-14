<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\Functional\Controller;

use OpenSolid\Api\Routing\Attribute\Delete;

#[Delete(
    path: '/items/{id}',
    name: 'func_delete_item',
)]
final readonly class DeleteItemController
{
    public function __invoke(string $id): void
    {
    }
}
