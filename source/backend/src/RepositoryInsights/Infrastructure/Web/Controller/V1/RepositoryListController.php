<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Web\Controller\V1;

use App\RepositoryInsights\Application\UseCase\ListRepositories\ListRepositoriesHandler;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Mapper\RepositoryResponseMapper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/repositories', name: 'api_v1_repositories_')]
final readonly class RepositoryListController
{
    public function __construct(
        private ListRepositoriesHandler $handler,
        private RepositoryResponseMapper $responseMapper,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $response = ($this->handler)();

        return new JsonResponse($this->responseMapper->listResponse($response));
    }
}
