<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Web\Controller\V1;

use App\RepositoryInsights\Application\UseCase\SyncRepository\SyncRepositoryHandler;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Mapper\RepositoryResponseMapper;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto\RepositorySyncRequestDto;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Mapper\RepositorySyncRequestMapper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/repositories', name: 'api_v1_repositories_')]
final readonly class RepositorySyncController
{
    public function __construct(
        private SyncRepositoryHandler $handler,
        private RepositorySyncRequestMapper $requestMapper,
        private RepositoryResponseMapper $responseMapper,
    ) {
    }

    #[Route('/{id<\\d+>}/sync', name: 'sync', methods: ['POST'])]
    public function __invoke(
        int $id,
        #[MapRequestPayload(acceptFormat: 'json', validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        ?RepositorySyncRequestDto $requestDto = null,
    ): JsonResponse {
        $request = $this->requestMapper->mapToApplication($id, $requestDto);
        $response = ($this->handler)($request);

        return new JsonResponse($this->responseMapper->importResult($response));
    }
}
