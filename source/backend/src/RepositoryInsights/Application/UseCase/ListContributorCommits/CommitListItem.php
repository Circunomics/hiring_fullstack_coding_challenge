<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Application\UseCase\ListContributorCommits;

use DateTimeImmutable;

final readonly class CommitListItem
{
    public function __construct(
        public int $id,
        public string $sha,
        public string $message,
        public DateTimeImmutable $committedAt,
        public string $htmlUrl,
        public string $authorName,
        public ?string $authorEmail,
    ) {
    }
}
