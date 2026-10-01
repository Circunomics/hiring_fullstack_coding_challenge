<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto;

final readonly class ContributorCommitItemResponseDto
{
    public function __construct(
        public int $id,
        public string $sha,
        public string $shortSha,
        public string $message,
        public string $committedAt,
        public string $htmlUrl,
        public string $authorName,
        public ?string $authorEmail,
    ) {
    }
}
