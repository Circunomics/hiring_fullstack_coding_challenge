<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Application\UseCase\ImportRepository;

final readonly class ImportRepositoryResponse
{
    public function __construct(
        public int $repositoryId,
        public int $seen,
        public int $inserted,
        public int $skippedDuplicates,
    ) {
    }
}
