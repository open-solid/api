<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0',
    description: 'My API description',
    title: 'My First API',
)]
#[OA\License(name: 'MIT', url: 'https://opensource.org/licenses/MIT')]
#[OA\Server(url: 'https://127.0.0.1:8000', description: 'Production server (uses live data)')]
#[OA\Tag(name: 'Product', description: 'The product resource')]
class OpenApiSpec
{
}
