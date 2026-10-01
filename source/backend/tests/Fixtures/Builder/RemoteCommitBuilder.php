<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\Builder;

use App\RepositoryInsights\Application\Dto\RemoteCommit;
use App\RepositoryInsights\Domain\ValueObject\CommitSha;
use DateTimeImmutable;

final class RemoteCommitBuilder
{
    private string $sha = 'a1b2c3d4e5f60718293a4b5c6d7e8f9012345678';

    private string $authorName = 'Alice Example';

    private ?string $authorEmail = 'alice@example.com';

    private string $message = 'Add feature';

    private DateTimeImmutable $committedAt;

    private string $htmlUrl = 'https://github.com/org/repo/commit/deadbeef';

    private ?string $authorAvatarUrl = null;

    public static function create(): self
    {
        $b = new self();
        $b->committedAt = new DateTimeImmutable('2026-01-15T10:00:00Z');

        return $b;
    }

    public function withSha(string $sha): self
    {
        $c = clone $this;
        $c->sha = $sha;

        return $c;
    }

    public function withAuthor(string $name, ?string $email): self
    {
        $c = clone $this;
        $c->authorName = $name;
        $c->authorEmail = $email;

        return $c;
    }

    public function withCommittedAt(DateTimeImmutable $at): self
    {
        $c = clone $this;
        $c->committedAt = $at;

        return $c;
    }

    public function withAvatar(?string $url): self
    {
        $c = clone $this;
        $c->authorAvatarUrl = $url;

        return $c;
    }

    public function build(): RemoteCommit
    {
        return new RemoteCommit(
            sha: new CommitSha($this->sha),
            authorName: $this->authorName,
            authorEmail: $this->authorEmail,
            message: $this->message,
            committedAt: $this->committedAt,
            htmlUrl: $this->htmlUrl,
            authorAvatarUrl: $this->authorAvatarUrl,
        );
    }
}
