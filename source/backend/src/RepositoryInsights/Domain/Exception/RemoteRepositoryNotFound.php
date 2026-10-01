<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Domain\Exception;

use App\RepositoryInsights\Domain\ValueObject\RepositoryCoordinates;
use RuntimeException;

final class RemoteRepositoryNotFound extends RuntimeException
{
    public static function at(RepositoryCoordinates $coords): self
    {
        return new self(sprintf(
            'Repository "%s/%s" not found on %s.',
            $coords->owner, $coords->name, $coords->provider->value,
        ));
    }
}
