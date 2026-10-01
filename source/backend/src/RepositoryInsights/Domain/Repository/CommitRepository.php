<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Domain\Repository;

use App\RepositoryInsights\Domain\Entity\Commit;
use App\RepositoryInsights\Domain\Entity\TrackedRepository;
use DateTimeImmutable;

interface CommitRepository
{
    public function save(Commit $commit): void;

    /**
     *
     * @return list<string>
     */
    public function existingShasFor(TrackedRepository $repository): array;

    /**
     *
     * @return array{
     *     items: list<array{id:int, displayName:string, email:?string, avatarUrl:?string, commitCount:int, lastCommittedAt:\DateTimeImmutable}>,
     *     total: int
     * }
     */
    public function aggregateContributors(
        TrackedRepository $repository,
        ?string $search,
        ?DateTimeImmutable $from,
        ?DateTimeImmutable $to,
        string $sort,
        string $direction,
        int $page,
        int $perPage,
    ): array;

    /**
     * @return array{
     *     items: list<array{id:int, sha:string, message:string, committedAt:\DateTimeImmutable, htmlUrl:string, authorName:string, authorEmail:?string}>,
     *     total: int
     * }
     */
    public function commitsByContributor(
        TrackedRepository $repository,
        int $contributorId,
        int $page,
        int $perPage,
    ): array;
}
