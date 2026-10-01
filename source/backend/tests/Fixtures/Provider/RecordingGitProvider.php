<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\Provider;

use App\RepositoryInsights\Application\Dto\RemoteCommit;
use App\RepositoryInsights\Application\Service\CommitProvider;
use App\RepositoryInsights\Domain\Enum\GitProvider as GitProviderEnum;
use App\RepositoryInsights\Domain\ValueObject\RepositoryCoordinates;

final class RecordingGitProvider implements CommitProvider
{
    public int $callCount = 0;

    /**
     * @param list<RemoteCommit> $commits
     */
    public function __construct(private array $commits, private readonly GitProviderEnum $id = GitProviderEnum::Github)
    {
    }

    public function providerId(): GitProviderEnum
    {
        return $this->id;
    }

    public function fetchRecentCommits(RepositoryCoordinates $coords, int $maxCommits): iterable
    {
        ++$this->callCount;
        $yielded = 0;
        foreach ($this->commits as $commit) {
            if ($yielded >= $maxCommits) {
                return;
            }

            ++$yielded;
            yield $commit;
        }
    }

    /**
     * @param list<RemoteCommit> $commits
     */
    public function withCommits(array $commits): void
    {
        $this->commits = $commits;
    }
}
