<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Application\UseCase\ListRepositories;

final readonly class ListRepositoriesResponse
{
    /**
     * @param list<RepositoryListItem> $items
     */
    public function __construct(public array $items)
    {
    }
}
