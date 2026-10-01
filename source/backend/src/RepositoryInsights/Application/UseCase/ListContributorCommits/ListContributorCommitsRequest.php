<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Application\UseCase\ListContributorCommits;

use Webmozart\Assert\Assert;

final readonly class ListContributorCommitsRequest
{
    public function __construct(
        public int $repositoryId,
        public int $contributorId,
        public int $page = 1,
        public int $perPage = 20,
    ) {
        Assert::positiveInteger($repositoryId);
        Assert::positiveInteger($contributorId);
        Assert::positiveInteger($page);
        Assert::range($perPage, 1, 100);
    }
}
