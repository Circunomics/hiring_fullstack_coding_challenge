<?php

declare(strict_types=1);

namespace App\Tests\Unit\RepositoryInsights\Infrastructure\Web\Presentation\V1\Mapper;

use App\RepositoryInsights\Application\UseCase\ImportRepository\ImportRepositoryResponse;
use App\RepositoryInsights\Application\UseCase\ListContributorCommits\CommitListItem;
use App\RepositoryInsights\Application\UseCase\ListContributorCommits\ListContributorCommitsResponse;
use App\RepositoryInsights\Application\UseCase\ListContributors\ContributorListItem;
use App\RepositoryInsights\Application\UseCase\ListContributors\ListContributorsResponse;
use App\RepositoryInsights\Application\UseCase\ListRepositories\ListRepositoriesResponse;
use App\RepositoryInsights\Application\UseCase\ListRepositories\RepositoryListItem;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Mapper\ContributorResponseMapper;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Mapper\RepositoryResponseMapper;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(RepositoryResponseMapper::class)]
#[CoversClass(ContributorResponseMapper::class)]
final class ResponseMapperTest extends TestCase
{
    #[TestDox('response mappers translate application results into API DTOs')]
    public function testMapsApplicationResponsesToApiDtos(): void
    {
        $repositoryMapper = new RepositoryResponseMapper();
        $repositoryList = $repositoryMapper->listResponse(new ListRepositoriesResponse([
            new RepositoryListItem(7, 'github', 'octo', 'demo', 'success', new DateTimeImmutable('2026-02-01T10:00:00Z'), null, 2),
        ]));
        $importResult = $repositoryMapper->importResult(new ImportRepositoryResponse(7, 2, 2, 0));

        $contributorMapper = new ContributorResponseMapper();
        $contributorList = $contributorMapper->listResponse(new ListContributorsResponse([
            new ContributorListItem(3, 'Alice', 'alice@example.com', null, 2, new DateTimeImmutable('2026-02-01T10:00:00Z')),
        ], 1, 1, 20));
        $commitList = $contributorMapper->commitsResponse(new ListContributorCommitsResponse([
            new CommitListItem(9, str_repeat('a', 40), 'Add feature', new DateTimeImmutable('2026-02-01T10:00:00Z'), 'https://example.test/commit', 'Alice', 'alice@example.com'),
        ], 1, 1, 20));

        self::assertSame('octo/demo', $repositoryList->items[0]->fullName);
        self::assertSame('2026-02-01T10:00:00+00:00', $repositoryList->items[0]->lastSyncedAt);
        self::assertSame(2, $importResult->inserted);
        self::assertSame('Alice', $contributorList->items[0]->displayName);
        self::assertSame(str_repeat('a', 40), $commitList->items[0]->sha);
        self::assertSame('aaaaaaa', $commitList->items[0]->shortSha);
        self::assertSame('https://example.test/commit', $commitList->items[0]->htmlUrl);
    }
}
