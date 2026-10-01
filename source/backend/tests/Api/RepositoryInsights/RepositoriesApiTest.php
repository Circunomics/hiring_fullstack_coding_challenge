<?php

declare(strict_types=1);

namespace App\Tests\Api\RepositoryInsights;

use App\Tests\Api\ApiTestCase;
use App\Tests\Fixtures\Builder\RemoteCommitBuilder;
use App\Tests\Fixtures\Provider\RecordingGitProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;

final class RepositoriesApiTest extends ApiTestCase
{
    private RecordingGitProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = self::getContainer()->get(RecordingGitProvider::class);
    }

    #[TestDox('POST /api/v1/repositories imports commits and returns them in the repository list')]
    public function testImportThenList(): void
    {
        $this->provider->withCommits([
            RemoteCommitBuilder::create()->withSha(str_repeat('a', 40))->build(),
            RemoteCommitBuilder::create()->withSha(str_repeat('b', 40))->withAuthor('Bob', 'bob@example.com')->build(),
        ]);

        $import = $this->request('POST', '/api/v1/repositories', [
            'slug' => 'codex-test-owner/repository-insights-api',
        ]);
        self::assertSame(Response::HTTP_CREATED, $import->getStatusCode());
        self::assertSame(2, $this->jsonBody($import)['inserted']);

        $list = $this->request('GET', '/api/v1/repositories');
        self::assertSame(Response::HTTP_OK, $list->getStatusCode());
        $items = $this->jsonBody($list)['items'];
        $importedRepository = array_find($items, static fn (array $item): bool => $item['fullName'] === 'codex-test-owner/repository-insights-api');

        self::assertNotNull($importedRepository);
        self::assertSame(2, $importedRepository['commitCount']);
    }
}
