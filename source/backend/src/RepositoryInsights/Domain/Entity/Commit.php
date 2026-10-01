<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Domain\Entity;

use App\RepositoryInsights\Domain\ValueObject\CommitSha;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Entity]
#[ORM\Table(name: 'commits')]
#[ORM\UniqueConstraint(name: 'uniq_commit_repo_sha', columns: ['repository_id', 'sha'])]
#[ORM\Index(name: 'idx_commit_committed_at', columns: ['committed_at'])]
#[ORM\Index(name: 'idx_commit_repo_contributor', columns: ['repository_id', 'contributor_id'])]
class Commit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: TrackedRepository::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private TrackedRepository $repository;

    #[ORM\ManyToOne(targetEntity: Contributor::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Contributor $contributor;

    #[ORM\Column(length: 40)]
    private string $sha;

    /** Raw author metadata from this Git commit, retained as historical data. */
    #[ORM\Column(name: 'author_name', length: 255)]
    private string $gitAuthorName;

    /** Raw author metadata from this Git commit, retained as historical data. */
    #[ORM\Column(name: 'author_email', length: 320, nullable: true)]
    private ?string $gitAuthorEmail;

    #[ORM\Column(type: Types::TEXT)]
    private string $message;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $committedAt;

    #[ORM\Column(length: 512)]
    private string $htmlUrl;

    public function __construct(
        TrackedRepository $repository,
        Contributor $contributor,
        CommitSha $sha,
        string $gitAuthorName,
        ?string $gitAuthorEmail,
        string $message,
        DateTimeImmutable $committedAt,
        string $htmlUrl,
    ) {
        Assert::notEmpty(trim($gitAuthorName), 'A commit must have an author name.');
        Assert::maxLength($gitAuthorName, 255);
        Assert::nullOrMaxLength($gitAuthorEmail, 320);
        Assert::notEmpty($htmlUrl);
        Assert::maxLength($htmlUrl, 512);

        $this->repository = $repository;
        $this->contributor = $contributor;
        $this->sha = $sha->value;
        $this->gitAuthorName = $gitAuthorName;
        $this->gitAuthorEmail = $gitAuthorEmail;
        $this->message = $message;
        $this->committedAt = $committedAt;
        $this->htmlUrl = $htmlUrl;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRepository(): TrackedRepository
    {
        return $this->repository;
    }

    public function getContributor(): Contributor
    {
        return $this->contributor;
    }

    public function getSha(): CommitSha
    {
        return new CommitSha($this->sha);
    }

    public function getShaValue(): string
    {
        return $this->sha;
    }

    public function getGitAuthorName(): string
    {
        return $this->gitAuthorName;
    }

    public function getGitAuthorEmail(): ?string
    {
        return $this->gitAuthorEmail;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCommittedAt(): DateTimeImmutable
    {
        return $this->committedAt;
    }

    public function getHtmlUrl(): string
    {
        return $this->htmlUrl;
    }
}
