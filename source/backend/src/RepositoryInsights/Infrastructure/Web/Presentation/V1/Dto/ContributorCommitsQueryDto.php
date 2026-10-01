<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto;

final readonly class ContributorCommitsQueryDto
{
    public function __construct(
        public int $page = 1,
        public int $perPage = 20,
    ) {
    }
}
