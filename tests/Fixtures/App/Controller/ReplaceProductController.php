<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App\Controller;

use OpenApi\Attributes\PathParameter;
use OpenSolid\Api\Routing\Attribute\Put;
use OpenSolid\Api\Tests\Fixtures\App\Model\ProductId;
use OpenSolid\Api\Tests\Fixtures\App\Model\ProductView;
use OpenSolid\Api\Tests\Fixtures\App\Model\ReplaceProductPayload;
use OpenSolid\Core\Domain\Model\GetOrCreateResource;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[Put(
    path: '/products/{id}',
    name: 'api_replace_product',
    description: 'Replace a Product',
    summary: 'Replaces an existing product or creates it if it does not exist.',
    tags: ['Product'],
)]
final readonly class ReplaceProductController
{
    /**
     * @return GetOrCreateResource<ProductView>
     */
    public function __invoke(#[PathParameter] ProductId $id, #[MapRequestPayload] ReplaceProductPayload $payload): GetOrCreateResource
    {
        throw new \LogicException('Not implemented.');
    }
}
