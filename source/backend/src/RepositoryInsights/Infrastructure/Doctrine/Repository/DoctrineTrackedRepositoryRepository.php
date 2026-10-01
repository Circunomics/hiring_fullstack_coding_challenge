<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Doctrine\Repository;

use App\RepositoryInsights\Domain\Entity\TrackedRepository;
use App\RepositoryInsights\Domain\Enum\SyncStatus;
use App\RepositoryInsights\Domain\Exception\TrackedRepositoryNotFound;
use App\RepositoryInsights\Domain\Repository\TrackedRepositoryRepository;
use App\RepositoryInsights\Domain\ValueObject\RepositoryCoordinates;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineTrackedRepositoryRepository implements TrackedRepositoryRepository
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function save(TrackedRepository $repository): void
    {
        $this->em->persist($repository);
    }

    public function findByCoordinates(RepositoryCoordinates $coords): ?TrackedRepository
    {
        return $this->em->getRepository(TrackedRepository::class)->findOneBy([
            'provider' => $coords->provider,
            'owner' => $coords->owner,
            'name' => $coords->name,
        ]);
    }

    public function getById(int $id): TrackedRepository
    {
        $repository = $this->em->find(TrackedRepository::class, $id);
        if ($repository === null) {
            throw TrackedRepositoryNotFound::byId($id);
        }

        return $repository;
    }

    public function listWithCommitCount(): array
    {
        $rows = $this->em->createQueryBuilder()
            ->from(TrackedRepository::class, 'r')
            ->leftJoin(\App\RepositoryInsights\Domain\Entity\Commit::class, 'c', 'WITH', 'c.repository = r')
            ->select(
                'r.id AS id',
                'r.provider AS provider',
                'r.owner AS owner',
                'r.name AS name',
                'r.syncStatus AS syncStatus',
                'r.lastSyncedAt AS lastSyncedAt',
                'r.lastSyncError AS lastSyncError',
                'COUNT(c.id) AS commitCount',
            )
            ->groupBy('r.id')
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getArrayResult();

        $normalized = [];
        foreach ($rows as $row) {
            /** @var SyncStatus $status */
            $status = $row['syncStatus'];
            /** @var \App\RepositoryInsights\Domain\Enum\GitProvider $providerEnum */
            $providerEnum = $row['provider'];
            $lastSyncedAt = $row['lastSyncedAt'];
            \assert($lastSyncedAt === null || $lastSyncedAt instanceof \DateTimeImmutable);
            $lastSyncError = $row['lastSyncError'];
            \assert($lastSyncError === null || \is_string($lastSyncError));

            $normalized[] = [
                'id' => (int) $row['id'],
                'provider' => $providerEnum->value,
                'owner' => (string) $row['owner'],
                'name' => (string) $row['name'],
                'syncStatus' => $status->value,
                'lastSyncedAt' => $lastSyncedAt,
                'lastSyncError' => $lastSyncError,
                'commitCount' => (int) $row['commitCount'],
            ];
        }

        return $normalized;
    }
}
