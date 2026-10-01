<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Domain\Repository;

use App\RepositoryInsights\Domain\Entity\TrackedRepository;
use App\RepositoryInsights\Domain\Exception\TrackedRepositoryNotFound;
use App\RepositoryInsights\Domain\ValueObject\RepositoryCoordinates;

interface TrackedRepositoryRepository
{
    public function save(TrackedRepository $repository): void;

    public function findByCoordinates(RepositoryCoordinates $coords): ?TrackedRepository;

    /** @throws TrackedRepositoryNotFound */
    public function getById(int $id): TrackedRepository;

    /**
     *
     * @return list<array{
     *     id: int,
     *     provider: string,
     *     owner: string,
     *     name: string,
     *     syncStatus: string,
     *     lastSyncedAt: ?\DateTimeImmutable,
     *     lastSyncError: ?string,
     *     commitCount: int
     * }>
     */
    public function listWithCommitCount(): array;
}
