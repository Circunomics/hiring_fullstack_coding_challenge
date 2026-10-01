<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Mapper;

use App\RepositoryInsights\Application\UseCase\ListContributorCommits\CommitListItem;
use App\RepositoryInsights\Application\UseCase\ListContributorCommits\ListContributorCommitsResponse;
use App\RepositoryInsights\Application\UseCase\ListContributors\ContributorListItem;
use App\RepositoryInsights\Application\UseCase\ListContributors\ListContributorsResponse;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto\ContributorCommitItemResponseDto;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto\ContributorCommitsResponseDto;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto\ContributorListItemResponseDto;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto\ContributorListResponseDto;
use DateTimeImmutable;

final class ContributorResponseMapper
{
    public function listResponse(ListContributorsResponse $response): ContributorListResponseDto
    {
        return new ContributorListResponseDto(
            items: array_map(
                $this->listItem(...),
                $response->items,
            ),
            total: $response->total,
            page: $response->page,
            perPage: $response->perPage,
        );
    }

    public function listItem(ContributorListItem $item): ContributorListItemResponseDto
    {
        return new ContributorListItemResponseDto(
            id: $item->id,
            displayName: $item->displayName,
            email: $item->email,
            avatarUrl: $item->avatarUrl,
            commitCount: $item->commitCount,
            lastCommittedAt: $item->lastCommittedAt->format(DateTimeImmutable::ATOM),
        );
    }

    public function commitsResponse(ListContributorCommitsResponse $response): ContributorCommitsResponseDto
    {
        return new ContributorCommitsResponseDto(
            items: array_map(
                $this->commitItem(...),
                $response->items,
            ),
            total: $response->total,
            page: $response->page,
            perPage: $response->perPage,
        );
    }

    public function commitItem(CommitListItem $item): ContributorCommitItemResponseDto
    {
        return new ContributorCommitItemResponseDto(
            id: $item->id,
            sha: $item->sha,
            shortSha: substr($item->sha, 0, 7),
            message: $item->message,
            committedAt: $item->committedAt->format(DateTimeImmutable::ATOM),
            htmlUrl: $item->htmlUrl,
            authorName: $item->authorName,
            authorEmail: $item->authorEmail,
        );
    }
}
