<?php

declare(strict_types=1);

namespace OpenSolid\Api\OpenApi\Schema;

use OpenApi\Attributes as OA;

class PaginatorSchema extends OA\Schema
{
    /**
     * @param class-string $itemRef
     */
    public function __construct(string $itemRef)
    {
        parent::__construct(
            properties: [
                new OA\Property(
                    property: 'items',
                    description: 'The list of items',
                    type: 'array',
                    items: new OA\Items(ref: $itemRef),
                ),
                new OA\Property(property: 'totalItems', description: 'The total number of items', type: 'integer', example: 1),
            ],
            type: 'object',
        );
    }
}
