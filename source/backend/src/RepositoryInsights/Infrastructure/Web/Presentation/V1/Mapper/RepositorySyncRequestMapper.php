<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Mapper;

use App\RepositoryInsights\Application\UseCase\SyncRepository\SyncRepositoryRequest;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto\RepositorySyncRequestDto;

final readonly class RepositorySyncRequestMapper
{
    public function mapToApplication(int $repositoryId, ?RepositorySyncRequestDto $request): SyncRepositoryRequest
    {
        return new SyncRepositoryRequest($repositoryId, $request->maxCommits ?? 1000);
    }
}
