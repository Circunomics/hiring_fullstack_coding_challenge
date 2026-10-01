<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto;

final readonly class ContributorListQueryDto
{
    public function __construct(
        public ?string $search = null,
        public ?string $from = null,
        public ?string $to = null,
        public string $sort = 'commits',
        public string $direction = 'desc',
        public int $page = 1,
        public int $perPage = 20,
    ) {
    }
}
