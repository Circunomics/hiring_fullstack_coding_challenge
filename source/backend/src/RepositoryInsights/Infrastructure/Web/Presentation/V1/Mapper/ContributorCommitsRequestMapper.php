<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Mapper;

use App\RepositoryInsights\Application\UseCase\ListContributorCommits\ListContributorCommitsRequest;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto\ContributorCommitsQueryDto;

final readonly class ContributorCommitsRequestMapper
{
    public function mapToApplication(
        int $repositoryId,
        int $contributorId,
        ContributorCommitsQueryDto $query,
    ): ListContributorCommitsRequest {
        return new ListContributorCommitsRequest(
            repositoryId: $repositoryId,
            contributorId: $contributorId,
            page: $query->page,
            perPage: $query->perPage,
        );
    }
}
