<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Application\UseCase\ListContributors;

final readonly class ListContributorsResponse
{
    /**
     * @param list<ContributorListItem> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $perPage,
    ) {
    }
}
