<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Application\UseCase\ListRepositories;

use DateTimeImmutable;

final readonly class RepositoryListItem
{
    public function __construct(
        public int $id,
        public string $provider,
        public string $owner,
        public string $name,
        public string $syncStatus,
        public ?DateTimeImmutable $lastSyncedAt,
        public ?string $lastSyncError,
        public int $commitCount,
    ) {
    }
}
