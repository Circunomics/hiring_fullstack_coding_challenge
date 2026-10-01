<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Application\UseCase\ListContributorCommits;

use App\RepositoryInsights\Domain\Repository\CommitRepository;
use App\RepositoryInsights\Domain\Repository\TrackedRepositoryRepository;

final readonly class ListContributorCommitsHandler
{
    public function __construct(
        private TrackedRepositoryRepository $repositories,
        private CommitRepository $commits,
    ) {
    }

    public function __invoke(ListContributorCommitsRequest $request): ListContributorCommitsResponse
    {
        $repository = $this->repositories->getById($request->repositoryId);

        $result = $this->commits->commitsByContributor(
            repository: $repository,
            contributorId: $request->contributorId,
            page: $request->page,
            perPage: $request->perPage,
        );

        $items = array_map(
            static fn (array $row): CommitListItem => new CommitListItem(
                id: $row['id'],
                sha: $row['sha'],
                message: $row['message'],
                committedAt: $row['committedAt'],
                htmlUrl: $row['htmlUrl'],
                authorName: $row['authorName'],
                authorEmail: $row['authorEmail'],
            ),
            $result['items'],
        );

        return new ListContributorCommitsResponse(
            items: $items,
            total: $result['total'],
            page: $request->page,
            perPage: $request->perPage,
        );
    }
}
