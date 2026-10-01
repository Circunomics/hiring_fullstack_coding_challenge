<?php

declare(strict_types=1);

namespace App\Tests\Unit\RepositoryInsights\Infrastructure\Web\Presentation\V1\Mapper;

use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto\ContributorCommitsQueryDto;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto\ContributorListQueryDto;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto\RepositoryImportRequestDto;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto\RepositorySyncRequestDto;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Mapper\ContributorCommitsRequestMapper;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Mapper\ContributorListRequestMapper;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Mapper\RepositoryImportRequestMapper;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Mapper\RepositorySyncRequestMapper;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

final class RequestMapperTest extends TestCase
{
    #[TestDox('request mappers translate API DTOs into application requests')]
    public function testMapsRequestDtosToApplicationRequests(): void
    {
        $import = (new RepositoryImportRequestMapper())->mapToApplication(new RepositoryImportRequestDto(
            provider: 'github', slug: 'octo/demo', maxCommits: 50,
        ));
        $sync = (new RepositorySyncRequestMapper())->mapToApplication(12, new RepositorySyncRequestDto(25));
        $contributors = (new ContributorListRequestMapper())->mapToApplication(12, new ContributorListQueryDto(
            search: 'alice', from: '2026-02-01', to: '2026-02-28', sort: 'name', direction: 'asc', page: 2, perPage: 10,
        ));
        $commits = (new ContributorCommitsRequestMapper())->mapToApplication(12, 34, new ContributorCommitsQueryDto(3, 15));

        self::assertSame('octo/demo', $import->coordinates->getFullName());
        self::assertSame(50, $import->maxCommits);
        self::assertSame(25, $sync->maxCommits);
        self::assertSame('alice', $contributors->search);
        self::assertEquals(new DateTimeImmutable('2026-02-01'), $contributors->from);
        self::assertEquals(new DateTimeImmutable('2026-03-01'), $contributors->to);
        self::assertSame('asc', $contributors->direction);
        self::assertSame(2, $contributors->page);
        self::assertSame(12, $commits->repositoryId);
        self::assertSame(34, $commits->contributorId);
        self::assertSame(3, $commits->page);
        self::assertSame(15, $commits->perPage);
    }
}
