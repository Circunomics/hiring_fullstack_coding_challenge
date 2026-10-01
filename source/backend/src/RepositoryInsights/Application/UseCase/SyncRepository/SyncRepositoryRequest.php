<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Application\UseCase\SyncRepository;

use Webmozart\Assert\Assert;

final readonly class SyncRepositoryRequest
{
    public function __construct(public int $repositoryId, public int $maxCommits = 1000)
    {
        Assert::positiveInteger($repositoryId);
        Assert::greaterThan($maxCommits, 0);
        Assert::lessThanEq($maxCommits, 1000);
    }
}
