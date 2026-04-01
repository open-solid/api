<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App\Model;

use OpenApi\Attributes as OA;
use OpenSolid\Api\Controller\Model\Paginator\PaginationParams;

final class FindProductsQuery
{
    use PaginationParams;

    #[OA\QueryParameter(description: 'Filter by product name')]
    public ?string $name = null;
}
