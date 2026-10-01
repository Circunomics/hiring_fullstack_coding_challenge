<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Application\UseCase\ListContributors;

use DateTimeImmutable;
use Webmozart\Assert\Assert;

final readonly class ListContributorsRequest
{
    public const string SORT_COMMITS = 'commits';

    public const string SORT_NAME = 'name';

    public const string SORT_LAST_COMMIT = 'lastCommittedAt';

    public const array ALLOWED_SORTS = [self::SORT_COMMITS, self::SORT_NAME, self::SORT_LAST_COMMIT];

    public const array ALLOWED_DIRECTIONS = ['asc', 'desc'];

    public function __construct(
        public int $repositoryId,
        public ?string $search = null,
        public ?DateTimeImmutable $from = null,
        public ?DateTimeImmutable $to = null,
        public string $sort = self::SORT_COMMITS,
        public string $direction = 'desc',
        public int $page = 1,
        public int $perPage = 20,
    ) {
        Assert::positiveInteger($repositoryId);
        Assert::inArray($sort, self::ALLOWED_SORTS, sprintf('sort must be one of %s', implode(', ', self::ALLOWED_SORTS)));
        Assert::inArray(strtolower($direction), self::ALLOWED_DIRECTIONS, 'direction must be "asc" or "desc"');
        Assert::positiveInteger($page);
        Assert::range($perPage, 1, 100, 'perPage must be between 1 and 100');
    }
}
