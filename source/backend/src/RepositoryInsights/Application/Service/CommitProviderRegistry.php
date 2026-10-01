<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Application\Service;

use App\RepositoryInsights\Domain\Enum\GitProvider as GitProviderEnum;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class CommitProviderRegistry
{
    /** @var array<string, CommitProvider> */
    private array $byId;

    /** @param iterable<CommitProvider> $providers */
    public function __construct(iterable $providers)
    {
        $map = [];
        foreach ($providers as $provider) {
            $id = $provider->providerId()->value;
            Assert::keyNotExists($map, $id, sprintf('Duplicate CommitProvider implementation for "%s".', $id));
            $map[$id] = $provider;
        }

        Assert::notEmpty($map, 'No CommitProvider implementations were registered.');
        $this->byId = $map;
    }

    public function get(GitProviderEnum $provider): CommitProvider
    {
        return $this->byId[$provider->value]
            ?? throw new InvalidArgumentException(sprintf('Unknown git provider "%s".', $provider->value));
    }
}
