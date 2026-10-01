<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Domain\ValueObject;

use Webmozart\Assert\Assert;

final readonly class CommitSha
{
    public string $value;

    public function __construct(string $value)
    {
        Assert::regex($value, '/^[0-9a-f]{7,40}$/i', sprintf('"%s" is not a valid SHA-1 hex string.', $value));
        $this->value = strtolower($value);
    }
}
