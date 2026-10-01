<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Application\UseCase\ListContributors;

use App\RepositoryInsights\Domain\Repository\CommitRepository;
use App\RepositoryInsights\Domain\Repository\TrackedRepositoryRepository;

final readonly class ListContributorsHandler
{
    public function __construct(
        private TrackedRepositoryRepository $repositories,
        private CommitRepository $commits,
    ) {
    }

    public function __invoke(ListContributorsRequest $request): ListContributorsResponse
    {
        $repository = $this->repositories->getById($request->repositoryId);

        $result = $this->commits->aggregateContributors(
            repository: $repository,
            search: $request->search,
            from: $request->from,
            to: $request->to,
            sort: $request->sort,
            direction: $request->direction,
            page: $request->page,
            perPage: $request->perPage,
        );

        $items = array_map(
            static fn (array $row): ContributorListItem => new ContributorListItem(
                id: $row['id'],
                displayName: $row['displayName'],
                email: $row['email'],
                avatarUrl: $row['avatarUrl'],
                commitCount: $row['commitCount'],
                lastCommittedAt: $row['lastCommittedAt'],
            ),
            $result['items'],
        );

        return new ListContributorsResponse(
            items: $items,
            total: $result['total'],
            page: $request->page,
            perPage: $request->perPage,
        );
    }
}
