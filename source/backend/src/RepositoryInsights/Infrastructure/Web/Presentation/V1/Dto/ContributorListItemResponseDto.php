<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto;

final readonly class ContributorListItemResponseDto
{
    public function __construct(
        public int $id,
        public string $displayName,
        public ?string $email,
        public ?string $avatarUrl,
        public int $commitCount,
        public string $lastCommittedAt,
    ) {
    }
}
