<?php

declare(strict_types=1);

namespace OpenSolid\Api\Tests\Controller;

use OpenSolid\Api\Tests\Fixtures\Functional\FunctionalTestKernel;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiRouteControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected static function getKernelClass(): string
    {
        return FunctionalTestKernel::class;
    }

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        restore_exception_handler();
    }

    private function getJsonResponse(): array
    {
        return json_decode($this->client->getInternalResponse()->getContent(), true);
    }

    #[Test]
    public function getResource(): void
    {
        $this->client->request('GET', '/items/abc-123', server: [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/json');

        $data = $this->getJsonResponse();
        self::assertSame('abc-123', $data['id']);
        self::assertSame('Test Item', $data['name']);
        self::assertSame(1000, $data['price']);
    }

    #[Test]
    public function postResource(): void
    {
        $this->client->request('POST', '/items', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], content: json_encode([
            'name' => 'New Item',
            'price' => 1500,
        ]));

        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('Content-Type', 'application/json');

        $data = $this->getJsonResponse();
        self::assertSame('new-id', $data['id']);
        self::assertSame('New Item', $data['name']);
        self::assertSame(1500, $data['price']);
    }

    #[Test]
    public function patchResource(): void
    {
        $this->client->request('PATCH', '/items/abc-123', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], content: json_encode([
            'name' => 'Updated Item',
        ]));

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/json');

        $data = $this->getJsonResponse();
        self::assertSame('abc-123', $data['id']);
        self::assertSame('Updated Item', $data['name']);
    }

    #[Test]
    public function putResourceReturns201WhenCreated(): void
    {
        $this->client->request('PUT', '/items/abc-123', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], content: json_encode([
            'name' => 'Replaced Item',
            'price' => 3000,
        ]));

        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('Content-Type', 'application/json');

        $data = $this->getJsonResponse();
        self::assertSame('abc-123', $data['id']);
        self::assertSame('Replaced Item', $data['name']);
        self::assertSame(3000, $data['price']);
    }

    #[Test]
    public function deleteResourceReturns204(): void
    {
        $this->client->request('DELETE', '/items/abc-123', server: [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        self::assertResponseStatusCodeSame(204);
        self::assertSame('', $this->client->getInternalResponse()->getContent());
    }

    #[Test]
    public function getCollectionReturnsPaginatedResponse(): void
    {
        $this->client->request('GET', '/items', server: [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/json');

        $data = $this->getJsonResponse();
        self::assertArrayHasKey('items', $data);
        self::assertArrayHasKey('totalItems', $data);
        self::assertCount(2, $data['items']);
        self::assertSame(2, $data['totalItems']);
        self::assertSame('Item 1', $data['items'][0]['name']);
        self::assertSame('Item 2', $data['items'][1]['name']);
    }

    #[Test]
    public function responseIncludesVaryHeaders(): void
    {
        $this->client->request('GET', '/items/abc-123', server: [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $response = $this->client->getResponse();

        self::assertResponseIsSuccessful();
        self::assertNotEmpty($response->getVary());
        self::assertContains('Content-Type', $response->getVary());
        self::assertContains('Authorization', $response->getVary());
        self::assertContains('Origin', $response->getVary());
    }

    #[Test]
    public function postResourceWithCustomStatusCode(): void
    {
        $this->client->request('POST', '/items/import', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], content: '{}');

        self::assertResponseStatusCodeSame(202);
        self::assertResponseHeaderSame('Content-Type', 'application/json');

        $data = $this->getJsonResponse();
        self::assertSame('imported', $data['id']);
    }

    #[Test]
    public function controllerReturningJsonResponsePassesThrough(): void
    {
        $this->client->request('GET', '/health', server: [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        self::assertResponseIsSuccessful();

        $data = $this->getJsonResponse();
        self::assertSame('ok', $data['status']);
    }

    #[Test]
    public function wrongMethodReturns405(): void
    {
        $this->client->request('POST', '/items/abc-123', server: [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        self::assertResponseStatusCodeSame(405);
    }
}
