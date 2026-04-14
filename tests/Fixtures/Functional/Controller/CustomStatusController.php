<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\Functional\Controller;

use OpenSolid\Api\Routing\Attribute\Post;
use OpenSolid\Api\Tests\Fixtures\Functional\Model\ItemView;
use Symfony\Component\HttpFoundation\Request;

#[Post(
    path: '/items/import',
    name: 'func_import_items',
    statusCode: 202,
)]
final readonly class CustomStatusController
{
    public function __invoke(Request $request): ItemView
    {
        return new ItemView(id: 'imported', name: 'Imported Item', price: 500);
    }
}
