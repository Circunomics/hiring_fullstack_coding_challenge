<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Application\UseCase\ListContributors;

use DateTimeImmutable;

final readonly class ContributorListItem
{
    public function __construct(
        public int $id,
        public string $displayName,
        public ?string $email,
        public ?string $avatarUrl,
        public int $commitCount,
        public DateTimeImmutable $lastCommittedAt,
    ) {
    }
}
