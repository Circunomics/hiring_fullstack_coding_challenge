<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto;

final readonly class RepositoryImportResponseDto
{
    public function __construct(
        public int $repositoryId,
        public int $seen,
        public int $inserted,
        public int $skippedDuplicates,
    ) {
    }
}
