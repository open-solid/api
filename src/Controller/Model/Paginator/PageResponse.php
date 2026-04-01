<?php

declare(strict_types=1);

namespace OpenSolid\Api\Controller\Model\Paginator;

use Symfony\Component\JsonStreamer\Attribute\JsonStreamable;

/**
 * @template T of object
 */
#[JsonStreamable]
class PageResponse
{
    /**
     * @param iterable<T> $items
     */
    public function __construct(
        public iterable $items,
        public int $totalItems,
    ) {
    }
}
