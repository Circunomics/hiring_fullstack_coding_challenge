<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Application\UseCase\ListContributorCommits;

final readonly class ListContributorCommitsResponse
{
    /**
     * @param list<CommitListItem> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $perPage,
    ) {
    }
}
