<?php

declare(strict_types=1);

namespace OpenSolid\Api\OpenApi\Processor;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Generator;

/**
 * Sorts OpenAPI paths by HTTP method priority and path structure.
 *
 * Order: POST (create), GET collection, GET item/sub-resources, DELETE, PATCH, PUT, then the rest.
 * Within the same method priority, paths are sorted alphabetically.
 */
final readonly class SortPaths
{
    private const METHOD_PRIORITY = [
        'post' => 0,
        'get' => 1,
        'delete' => 2,
        'patch' => 3,
        'put' => 4,
        'head' => 5,
        'options' => 6,
        'trace' => 7,
    ];

    public function __invoke(Analysis $analysis): void
    {
        if (Generator::isDefault($analysis->openapi->paths)) {
            return;
        }

        $paths = $analysis->openapi->paths;

        usort($paths, function (OA\PathItem $a, OA\PathItem $b): int {
            $methodA = $this->getPrimaryMethod($a);
            $methodB = $this->getPrimaryMethod($b);

            $priorityA = self::METHOD_PRIORITY[$methodA] ?? 8;
            $priorityB = self::METHOD_PRIORITY[$methodB] ?? 8;

            // Different methods: sort by method priority
            if ($priorityA !== $priorityB) {
                return $priorityA <=> $priorityB;
            }

            // Same method: sort by path segments count (fewer segments first), then alphabetically
            $segmentsA = substr_count($a->path, '/');
            $segmentsB = substr_count($b->path, '/');

            return $segmentsA <=> $segmentsB ?: $a->path <=> $b->path;
        });

        $analysis->openapi->paths = $paths;
    }

    /**
     * Returns the highest-priority HTTP method defined on the path item.
     */
    private function getPrimaryMethod(OA\PathItem $pathItem): string
    {
        foreach (self::METHOD_PRIORITY as $method => $priority) {
            if (!Generator::isDefault($pathItem->$method)) {
                return $method;
            }
        }

        return 'unknown';
    }
}
