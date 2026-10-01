<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Application\Dto;

use App\RepositoryInsights\Domain\ValueObject\CommitSha;
use DateTimeImmutable;

final readonly class RemoteCommit
{
    public function __construct(
        public CommitSha $sha,
        public string $authorName,
        public ?string $authorEmail,
        public string $message,
        public DateTimeImmutable $committedAt,
        public string $htmlUrl,
        public ?string $authorAvatarUrl = null,
    ) {
    }
}
