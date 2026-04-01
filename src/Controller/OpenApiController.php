<?php

declare(strict_types=1);

namespace OpenSolid\Api\Controller;

use OpenSolid\Api\OpenApi\OpenApiGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class OpenApiController
{
    public function __construct(
        private OpenApiGenerator $generator,
    ) {
    }

    public function __invoke(Request $request, string $format = 'json'): Response
    {
        $openApi = $this->generator->generate();

        if ('yaml' === $format) {
            return new Response($openApi->toYaml(), headers: ['Content-Type' => 'application/x-yaml']);
        }

        return new JsonResponse($openApi->toJson(), json: true);
    }
}
