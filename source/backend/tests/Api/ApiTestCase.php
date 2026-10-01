<?php

declare(strict_types=1);

namespace App\Tests\Api;

use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Override;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shared setup for API tests: boots the kernel, exposes a KernelBrowser +
 * EntityManager, and offers helpers for JSON assertion.
 */
abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected EntityManagerInterface $em;

    #[Override]
    protected function setUp(): void
    {
        $this->client = self::createClient();
        // Keep the same container across requests so mutable test services
        // (such as RecordingGitProvider) retain their state.
        $this->client->disableReboot();

        $container = static::getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.orm.default_entity_manager');
        $this->em = $em;
    }

    /**
     * @return array<string, mixed>
     * @throws JsonException
     */
    protected function jsonBody(Response $response): array
    {
        $content = (string) $response->getContent();
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

        return $decoded;
    }

    /**
     * @param array<string, mixed>|null $body
     */
    protected function request(string $method, string $uri, ?array $body = null): Response
    {
        $this->client->request(
            $method,
            $uri,
            server: ['CONTENT_TYPE' => 'application/json'],
            content: $body === null ? '' : json_encode($body, JSON_THROW_ON_ERROR),
        );

        return $this->client->getResponse();
    }
}
