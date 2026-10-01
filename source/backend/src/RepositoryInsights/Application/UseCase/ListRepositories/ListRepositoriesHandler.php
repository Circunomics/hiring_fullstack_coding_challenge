<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Application\UseCase\ListRepositories;

use App\RepositoryInsights\Domain\Repository\TrackedRepositoryRepository;

final readonly class ListRepositoriesHandler
{
    public function __construct(private TrackedRepositoryRepository $repositories)
    {
    }

    public function __invoke(): ListRepositoriesResponse
    {
        $rows = $this->repositories->listWithCommitCount();
        $items = array_map(
            static fn (array $row): RepositoryListItem => new RepositoryListItem(
                id: $row['id'],
                provider: $row['provider'],
                owner: $row['owner'],
                name: $row['name'],
                syncStatus: $row['syncStatus'],
                lastSyncedAt: $row['lastSyncedAt'],
                lastSyncError: $row['lastSyncError'],
                commitCount: $row['commitCount'],
            ),
            $rows,
        );

        return new ListRepositoriesResponse($items);
    }
}
