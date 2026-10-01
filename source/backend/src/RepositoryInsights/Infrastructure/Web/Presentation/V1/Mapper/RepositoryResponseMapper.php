<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Mapper;

use App\RepositoryInsights\Application\UseCase\ImportRepository\ImportRepositoryResponse;
use App\RepositoryInsights\Application\UseCase\ListRepositories\ListRepositoriesResponse;
use App\RepositoryInsights\Application\UseCase\ListRepositories\RepositoryListItem;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto\RepositoryImportResponseDto;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto\RepositoryListItemResponseDto;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto\RepositoryListResponseDto;
use DateTimeImmutable;

final class RepositoryResponseMapper
{
    public function listResponse(ListRepositoriesResponse $response): RepositoryListResponseDto
    {
        return new RepositoryListResponseDto(array_map(
            $this->listItem(...),
            $response->items,
        ));
    }

    public function listItem(RepositoryListItem $item): RepositoryListItemResponseDto
    {
        return new RepositoryListItemResponseDto(
            id: $item->id,
            provider: $item->provider,
            owner: $item->owner,
            name: $item->name,
            fullName: $item->owner . '/' . $item->name,
            commitCount: $item->commitCount,
            syncStatus: $item->syncStatus,
            lastSyncedAt: $item->lastSyncedAt?->format(DateTimeImmutable::ATOM),
            lastSyncError: $item->lastSyncError,
        );
    }

    public function importResult(ImportRepositoryResponse $response): RepositoryImportResponseDto
    {
        return new RepositoryImportResponseDto(
            repositoryId: $response->repositoryId,
            seen: $response->seen,
            inserted: $response->inserted,
            skippedDuplicates: $response->skippedDuplicates,
        );
    }
}
