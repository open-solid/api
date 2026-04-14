<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\Functional\Controller;

use OpenSolid\Api\Routing\Attribute\Patch;
use OpenSolid\Api\Tests\Fixtures\Functional\Model\ItemView;
use Symfony\Component\HttpFoundation\Request;

#[Patch(
    path: '/items/{id}',
    name: 'func_update_item',
)]
final readonly class UpdateItemController
{
    public function __invoke(string $id, Request $request): ItemView
    {
        $data = json_decode($request->getContent(), true);

        return new ItemView(
            id: $id,
            name: $data['name'] ?? 'Original',
            price: $data['price'] ?? 1000,
        );
    }
}
