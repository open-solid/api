<?php

declare(strict_types=1);

namespace OpenSolid\Api\Routing\Attribute;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Patch extends ApiRoute
{
    public static function getMethod(): string
    {
        return 'PATCH';
    }
}
