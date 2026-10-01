<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Domain\ValueObject;

use App\RepositoryInsights\Domain\Enum\GitProvider;
use Webmozart\Assert\Assert;

final readonly class RepositoryCoordinates
{
    public function __construct(
        public GitProvider $provider,
        public string $owner,
        public string $name,
    ) {
        Assert::notEmpty($owner, 'Repository owner must not be empty.');
        Assert::notEmpty($name, 'Repository name must not be empty.');
        Assert::maxLength($owner, 100, 'Repository owner must be at most 100 characters.');
        Assert::maxLength($name, 100, 'Repository name must be at most 100 characters.');
        // GitHub allows [A-Za-z0-9._-] for both owner and name (with restrictions we don't enforce here).
        Assert::regex($owner, '/^[A-Za-z0-9](?:[A-Za-z0-9]|-(?=[A-Za-z0-9])){0,99}$/',
            sprintf('Invalid owner "%s".', $owner));
        Assert::regex($name, '/^[A-Za-z0-9._-]+$/',
            sprintf('Invalid repository name "%s".', $name));
    }

    public static function fromSlug(GitProvider $provider, string $slug): self
    {
        Assert::contains($slug, '/', 'Coordinates must be in "owner/repo" form.');
        [$owner, $name] = explode('/', $slug, 2);

        return new self($provider, $owner, $name);
    }

    public function getFullName(): string
    {
        return $this->owner . '/' . $this->name;
    }
}
