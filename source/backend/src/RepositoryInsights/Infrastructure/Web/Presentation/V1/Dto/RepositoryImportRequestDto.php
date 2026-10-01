<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto;

final readonly class RepositoryImportRequestDto
{
    public function __construct(
        public string $provider = 'github',
        public ?string $slug = null,
        public ?string $owner = null,
        public ?string $name = null,
        public int $maxCommits = 1000,
    ) {
    }
}
