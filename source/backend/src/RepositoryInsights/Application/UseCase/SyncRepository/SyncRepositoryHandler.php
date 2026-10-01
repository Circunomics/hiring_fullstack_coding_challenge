<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Application\UseCase\SyncRepository;

use App\RepositoryInsights\Application\UseCase\ImportRepository\ImportRepositoryHandler;
use App\RepositoryInsights\Application\UseCase\ImportRepository\ImportRepositoryRequest;
use App\RepositoryInsights\Application\UseCase\ImportRepository\ImportRepositoryResponse;
use App\RepositoryInsights\Domain\Repository\TrackedRepositoryRepository;
use Throwable;

final readonly class SyncRepositoryHandler
{
    public function __construct(
        private TrackedRepositoryRepository $repositories,
        private ImportRepositoryHandler $import,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(SyncRepositoryRequest $request): ImportRepositoryResponse
    {
        $repository = $this->repositories->getById($request->repositoryId);

        return ($this->import)(new ImportRepositoryRequest(
            coordinates: $repository->getCoordinates(),
            maxCommits: $request->maxCommits,
        ));
    }
}
