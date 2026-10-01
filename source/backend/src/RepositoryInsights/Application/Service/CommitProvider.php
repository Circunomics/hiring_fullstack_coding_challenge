<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Application\Service;

use App\RepositoryInsights\Application\Dto\RemoteCommit;
use App\RepositoryInsights\Domain\Enum\GitProvider as GitProviderEnum;
use App\RepositoryInsights\Domain\ValueObject\RepositoryCoordinates;

interface CommitProvider
{
    public function providerId(): GitProviderEnum;

    /** @return iterable<RemoteCommit> */
    public function fetchRecentCommits(RepositoryCoordinates $coordinates, int $maxCommits): iterable;
}
