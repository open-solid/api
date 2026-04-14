<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\Functional\Controller;

use OpenSolid\Api\Routing\Attribute\Get;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Get(
    path: '/health',
    name: 'func_health',
)]
final readonly class JsonResponseController
{
    public function __invoke(): JsonResponse
    {
        return new JsonResponse(['status' => 'ok']);
    }
}
