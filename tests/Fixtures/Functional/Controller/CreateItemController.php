<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\Functional\Controller;

use OpenSolid\Api\Routing\Attribute\Post;
use OpenSolid\Api\Tests\Fixtures\Functional\Model\ItemView;
use Symfony\Component\HttpFoundation\Request;

#[Post(
    path: '/items',
    name: 'func_create_item',
)]
final readonly class CreateItemController
{
    public function __invoke(Request $request): ItemView
    {
        $data = json_decode($request->getContent(), true);

        return new ItemView(id: 'new-id', name: $data['name'], price: $data['price']);
    }
}
