<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App\Model;

use OpenApi\Attributes as OA;
use OpenSolid\Api\Controller\Model\Paginator\PaginationParams;

final class FindOrdersQuery
{
    use PaginationParams;

    #[OA\QueryParameter(description: 'Filter by external system ID')]
    public ?string $externalId = null;

    #[OA\QueryParameter(description: 'Filter by order status')]
    public ?string $status = null;

    #[OA\QueryParameter(description: 'Filter by currency code')]
    public ?string $currency = null;
}
