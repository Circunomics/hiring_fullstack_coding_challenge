<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto;

final readonly class RepositorySyncRequestDto
{
    public function __construct(public int $maxCommits = 1000)
    {
    }
}
