<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Web\Controller\V1;

use App\RepositoryInsights\Application\UseCase\ImportRepository\ImportRepositoryHandler;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Mapper\RepositoryResponseMapper;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto\RepositoryImportRequestDto;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Mapper\RepositoryImportRequestMapper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/repositories', name: 'api_v1_repositories_')]
final readonly class RepositoryImportController
{
    public function __construct(
        private ImportRepositoryHandler $handler,
        private RepositoryImportRequestMapper $requestMapper,
        private RepositoryResponseMapper $responseMapper,
    ) {
    }

    #[Route('', name: 'import', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload(acceptFormat: 'json', validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        RepositoryImportRequestDto $requestDto,
    ): JsonResponse {
        $request = $this->requestMapper->mapToApplication($requestDto);
        $response = ($this->handler)($request);

        return new JsonResponse($this->responseMapper->importResult($response), Response::HTTP_CREATED);
    }
}
