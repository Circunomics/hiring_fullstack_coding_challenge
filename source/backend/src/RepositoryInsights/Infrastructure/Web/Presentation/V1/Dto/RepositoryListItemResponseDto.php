<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto;

final readonly class RepositoryListItemResponseDto
{
    public function __construct(
        public int $id,
        public string $provider,
        public string $owner,
        public string $name,
        public string $fullName,
        public int $commitCount,
        public string $syncStatus,
        public ?string $lastSyncedAt,
        public ?string $lastSyncError,
    ) {
    }
}
