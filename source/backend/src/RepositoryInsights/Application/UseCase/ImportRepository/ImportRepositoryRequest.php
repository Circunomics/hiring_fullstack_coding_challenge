<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Application\UseCase\ImportRepository;

use App\RepositoryInsights\Domain\ValueObject\RepositoryCoordinates;
use Webmozart\Assert\Assert;

final readonly class ImportRepositoryRequest
{
    public function __construct(
        public RepositoryCoordinates $coordinates,
        public int $maxCommits = 1000,
    ) {
        Assert::greaterThan($maxCommits, 0);
        Assert::lessThanEq($maxCommits, 1000, 'Only 1000 commits are supported per import.');
    }
}
