<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Domain\Entity;

use App\RepositoryInsights\Domain\ValueObject\ContributorIdentity;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Entity]
#[ORM\Table(name: 'contributors')]
#[ORM\UniqueConstraint(name: 'uniq_contributor_identity', columns: ['identity'])]
class Contributor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(length: 320)]
    private string $identity;

    #[ORM\Column(length: 512, nullable: true)]
    private ?string $avatarUrl = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $firstSeenAt;

    #[ORM\Column(length: 255)]
    private string $displayName;

    #[ORM\Column(length: 320, nullable: true)]
    private ?string $email;

    public function __construct(ContributorIdentity $identity, string $displayName, ?string $email = null)
    {
        Assert::notEmpty(trim($displayName), 'A contributor must have a display name.');
        Assert::maxLength($displayName, 255);
        Assert::nullOrMaxLength($email, 320);

        $this->identity = $identity->value;
        $this->displayName = $displayName;
        $this->email = $email;
        $this->firstSeenAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentity(): ContributorIdentity
    {
        return ContributorIdentity::fromValue($this->identity);
    }

    public function getIdentityValue(): string
    {
        return $this->identity;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function fillAvatarIfMissing(?string $avatarUrl): void
    {
        if ($avatarUrl !== null && $this->avatarUrl === null) {
            $this->avatarUrl = $avatarUrl;
        }
    }

    public function getFirstSeenAt(): DateTimeImmutable
    {
        return $this->firstSeenAt;
    }
}
