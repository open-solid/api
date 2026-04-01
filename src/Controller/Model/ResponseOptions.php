<?php

declare(strict_types=1);

namespace OpenSolid\Api\Controller\Model;

use Symfony\Component\TypeInfo\Type;

final readonly class ResponseOptions
{
    public function __construct(
        public mixed $response = null,
        public ?Type $type = null,
        public ?int $statusCode = null,
        public ?array $headers = null,
    ) {
    }
}
