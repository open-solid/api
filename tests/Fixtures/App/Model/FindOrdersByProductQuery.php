<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App\Model;

use OpenApi\Attributes as OA;
use OpenSolid\Api\Controller\Model\Paginator\PaginationParams;

final class FindOrdersByProductQuery
{
    use PaginationParams;

    #[OA\QueryParameter(description: 'Filter by external system ID')]
    public ?string $externalId = null;

    #[OA\QueryParameter(description: 'Filter by product ID')]
    public ?string $productId = null;
}
