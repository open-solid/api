<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\Functional\Controller;

use OpenSolid\Api\Routing\Attribute\Put;
use OpenSolid\Api\Tests\Fixtures\Functional\Model\ItemView;
use OpenSolid\Core\Domain\Model\GetOrCreateResource;
use Symfony\Component\HttpFoundation\Request;

#[Put(
    path: '/items/{id}',
    name: 'func_replace_item',
)]
final readonly class ReplaceItemController
{
    /**
     * @return GetOrCreateResource<ItemView>
     */
    public function __invoke(string $id, Request $request): GetOrCreateResource
    {
        $data = json_decode($request->getContent(), true);

        return GetOrCreateResource::created(
            new ItemView(id: $id, name: $data['name'], price: $data['price']),
        );
    }
}
