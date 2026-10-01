<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Domain\Entity;

use App\RepositoryInsights\Domain\Enum\GitProvider;
use App\RepositoryInsights\Domain\Enum\SyncStatus;
use App\RepositoryInsights\Domain\ValueObject\RepositoryCoordinates;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use LogicException;

#[ORM\Entity]
#[ORM\Table(name: 'tracked_repositories')]
#[ORM\UniqueConstraint(name: 'uniq_repo_provider_owner_name', columns: ['provider', 'owner', 'name'])]
class TrackedRepository
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(length: 32, enumType: GitProvider::class)]
    private GitProvider $provider;

    #[ORM\Column(length: 100)]
    private string $owner;

    #[ORM\Column(length: 100)]
    private string $name;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $lastSyncedAt = null;

    #[ORM\Column(length: 16, enumType: SyncStatus::class)]
    private SyncStatus $syncStatus = SyncStatus::Pending;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $lastSyncError = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $updatedAt;

    public function __construct(RepositoryCoordinates $coordinates)
    {
        $this->provider = $coordinates->provider;
        $this->owner = $coordinates->owner;
        $this->name = $coordinates->name;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCoordinates(): RepositoryCoordinates
    {
        return new RepositoryCoordinates($this->provider, $this->owner, $this->name);
    }

    public function getProvider(): GitProvider
    {
        return $this->provider;
    }

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSyncStatus(): SyncStatus
    {
        return $this->syncStatus;
    }

    public function getLastSyncedAt(): ?DateTimeImmutable
    {
        return $this->lastSyncedAt;
    }

    public function getLastSyncError(): ?string
    {
        return $this->lastSyncError;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function beginSync(): void
    {
        if ($this->syncStatus === SyncStatus::Running) {
            throw new LogicException('A repository synchronization is already running.');
        }

        $this->syncStatus = SyncStatus::Running;
        $this->touch();
    }

    public function completeSync(DateTimeImmutable $at): void
    {
        $this->assertSyncIsRunning();
        $this->syncStatus = SyncStatus::Success;
        $this->lastSyncedAt = $at;
        $this->lastSyncError = null;
        $this->touch();
    }

    public function failSync(string $errorMessage): void
    {
        $this->assertSyncIsRunning();
        $this->syncStatus = SyncStatus::Failed;
        $this->lastSyncError = $errorMessage;
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    private function assertSyncIsRunning(): void
    {
        if ($this->syncStatus !== SyncStatus::Running) {
            throw new LogicException('A repository synchronization must be running before it can finish.');
        }
    }
}
