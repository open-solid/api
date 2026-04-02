<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Fixtures\App\Controller;

use OpenSolid\Api\Routing\Attribute\Post;
use OpenSolid\Api\Tests\Fixtures\App\Model\CreateOrderPayload;
use OpenSolid\Api\Tests\Fixtures\App\Model\OrderView;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[Post(
    path: '/orders',
    name: 'api_create_order',
    description: 'Create an Order',
    summary: 'Creates a new order resource.',
    tags: ['Order'],
)]
final readonly class CreateOrderController
{
    public function __invoke(#[MapRequestPayload] CreateOrderPayload $payload): OrderView
    {
        throw new \LogicException('Not implemented.');
    }
}
