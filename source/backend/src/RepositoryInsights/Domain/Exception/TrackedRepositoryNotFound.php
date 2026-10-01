<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Domain\Exception;

use App\RepositoryInsights\Domain\ValueObject\RepositoryCoordinates;
use RuntimeException;

final class TrackedRepositoryNotFound extends RuntimeException
{
    public static function byId(int $id): self
    {
        return new self(sprintf('Tracked repository #%d does not exist.', $id));
    }

    public static function byCoordinates(RepositoryCoordinates $coords): self
    {
        return new self(sprintf(
            'Tracked repository %s/%s on %s is not tracked yet.',
            $coords->owner, $coords->name, $coords->provider->value,
        ));
    }
}
