<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\GitProvider\GitHub;

use App\RepositoryInsights\Application\Dto\RemoteCommit;
use App\RepositoryInsights\Application\Service\CommitProvider;
use App\RepositoryInsights\Domain\Enum\GitProvider as GitProviderEnum;
use App\RepositoryInsights\Domain\Exception\ProviderRateLimited;
use App\RepositoryInsights\Domain\Exception\RemoteRepositoryNotFound;
use App\RepositoryInsights\Domain\ValueObject\CommitSha;
use App\RepositoryInsights\Domain\ValueObject\RepositoryCoordinates;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class GitHubGitProvider implements CommitProvider
{
    private const int PER_PAGE = 100;

    private const string API_BASE = 'https://api.github.com';

    private LoggerInterface $log;

    public function __construct(
        private HttpClientInterface $client,
        private ?string $token = null,
        ?LoggerInterface $log = null,
    ) {
        $this->log = $log ?? new NullLogger();
    }

    public function providerId(): GitProviderEnum
    {
        return GitProviderEnum::Github;
    }

    public function fetchRecentCommits(RepositoryCoordinates $coords, int $maxCommits): iterable
    {
        $yielded = 0;
        $page = 1;

        while ($yielded < $maxCommits) {
            $wantThisPage = min(self::PER_PAGE, $maxCommits - $yielded);

            $response = $this->client->request(
                'GET',
                sprintf('%s/repos/%s/%s/commits', self::API_BASE, $coords->owner, $coords->name),
                [
                    'headers' => $this->headers(),
                    'query' => ['per_page' => $wantThisPage, 'page' => $page],
                ],
            );

            $status = $response->getStatusCode();

            if ($status === 404) {
                throw RemoteRepositoryNotFound::at($coords);
            }

            if ($status === 403 || $status === 429) {
                $headers = $response->getHeaders(throw: false);
                $remaining = (int) ($headers['x-ratelimit-remaining'][0] ?? 1);
                if ($remaining === 0) {
                    $resetsAt = isset($headers['x-ratelimit-reset'][0])
                        ? new DateTimeImmutable('@' . $headers['x-ratelimit-reset'][0])
                        : null;
                    throw new ProviderRateLimited(GitProviderEnum::Github, $resetsAt);
                }
            }

            if ($status >= 400) {
                try {
                    $response->getContent();
                } catch (HttpExceptionInterface $e) {
                    throw new RuntimeException(sprintf('GitHub API error (%d): %s', $status, $e->getMessage()), $e->getCode(), previous: $e);
                }
            }

            /** @var list<array<string, mixed>> $payload */
            $payload = $response->toArray();
            $count = \count($payload);

            $this->log->debug('GitHub commits page fetched', [
                'coords' => $coords->getFullName(), 'page' => $page, 'received' => $count,
            ]);

            foreach ($payload as $row) {
                yield $this->toRemoteCommit($row);
                if (++$yielded >= $maxCommits) {
                    return;
                }
            }

            if ($count < $wantThisPage) {
                return;
            }

            $page++;
        }
    }

    /**
     * @param array<string, mixed> $row
     */
    private function toRemoteCommit(array $row): RemoteCommit
    {
        /** @var array<string, mixed> $commit */
        $commit = \is_array($row['commit'] ?? null) ? $row['commit'] : [];
        /** @var array<string, mixed> $author */
        $author = \is_array($commit['author'] ?? null) ? $commit['author'] : [];
        /** @var array<string, mixed>|null $rootAuthor */
        $rootAuthor = \is_array($row['author'] ?? null) ? $row['author'] : null;

        $email = isset($author['email']) && \is_string($author['email']) && $author['email'] !== ''
            ? $author['email']
            : null;

        $avatar = null;
        if ($rootAuthor !== null && isset($rootAuthor['avatar_url']) && \is_string($rootAuthor['avatar_url'])) {
            $avatar = $rootAuthor['avatar_url'];
        }

        return new RemoteCommit(
            sha: new CommitSha((string) ($row['sha'] ?? '')),
            authorName: (string) ($author['name'] ?? 'Unknown'),
            authorEmail: $email,
            message: (string) ($commit['message'] ?? ''),
            committedAt: new DateTimeImmutable((string) ($author['date'] ?? '1970-01-01T00:00:00Z')),
            htmlUrl: (string) ($row['html_url'] ?? ''),
            authorAvatarUrl: $avatar,
        );
    }

    /**
     * @return array<string, string>
     */
    private function headers(): array
    {
        $headers = [
            'Accept' => 'application/vnd.github+json',
            'X-GitHub-Api-Version' => '2022-11-28',
            'User-Agent' => 'circunomics-challenge-importer',
        ];
        if ($this->token !== null && $this->token !== '') {
            $headers['Authorization'] = 'Bearer ' . $this->token;
        }

        return $headers;
    }
}
