<?php

declare(strict_types=1);

namespace OpenSolid\Api\Controller\Model\Paginator;

use OpenApi\Attributes\QueryParameter;
use OpenApi\Attributes\Schema;

trait PaginationParams
{
    #[QueryParameter(
        description: 'Page number',
        schema: new Schema(minimum: 1),
    )]
    public int $page = 1;

    #[QueryParameter(
        description: 'Number of items per page',
        schema: new Schema(maximum: 100, minimum: 1),
    )]
    public int $itemsPerPage = 20;
}
