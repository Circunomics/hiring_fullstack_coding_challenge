<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Domain\Exception;

use App\RepositoryInsights\Domain\Enum\GitProvider;
use DateTimeImmutable;
use RuntimeException;

final class ProviderRateLimited extends RuntimeException
{
    public function __construct(
        public readonly GitProvider $provider,
        public readonly ?DateTimeImmutable $resetsAt,
    ) {
        $when = $resetsAt instanceof \DateTimeImmutable ? ' Retry after ' . $resetsAt->format('Y-m-d H:i:s') . '.' : '';
        parent::__construct(sprintf('Rate limit exceeded on %s.%s', $provider->value, $when));
    }
}
