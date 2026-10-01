<?php

declare(strict_types=1);

namespace App\Tests\Integration\RepositoryInsights\Infrastructure\Doctrine\Repository;

use App\RepositoryInsights\Domain\Entity\TrackedRepository;
use App\RepositoryInsights\Domain\Enum\GitProvider;
use App\RepositoryInsights\Domain\Enum\SyncStatus;
use App\RepositoryInsights\Domain\Repository\TrackedRepositoryRepository;
use App\RepositoryInsights\Domain\ValueObject\RepositoryCoordinates;
use App\RepositoryInsights\Infrastructure\Doctrine\Repository\DoctrineTrackedRepositoryRepository;
use App\Tests\Integration\DatabaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(DoctrineTrackedRepositoryRepository::class)]
final class DoctrineTrackedRepositoryRepositoryTest extends DatabaseTestCase
{
    #[TestDox('Doctrine persists and retrieves a tracked repository by its coordinates')]
    public function testSaveAndFind(): void
    {
        $coordinates = new RepositoryCoordinates(GitProvider::Github, 'octo', 'roundtrip');
        $repository = new TrackedRepository($coordinates);
        $repositories = self::getContainer()->get(TrackedRepositoryRepository::class);
        $repositories->save($repository);

        $this->em->flush();

        $found = $repositories->findByCoordinates($coordinates);

        self::assertNotNull($found);
        self::assertSame($repository->getId(), $found->getId());
        self::assertSame(SyncStatus::Pending, $found->getSyncStatus());
    }
}
