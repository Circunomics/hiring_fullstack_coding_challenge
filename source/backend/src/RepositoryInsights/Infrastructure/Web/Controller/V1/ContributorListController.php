<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Web\Controller\V1;

use App\RepositoryInsights\Application\UseCase\ListContributors\ListContributorsHandler;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Mapper\ContributorResponseMapper;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto\ContributorListQueryDto;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Mapper\ContributorListRequestMapper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/repositories/{repositoryId<\\d+>}', name: 'api_v1_repository_')]
final readonly class ContributorListController
{
    public function __construct(
        private ListContributorsHandler $handler,
        private ContributorListRequestMapper $requestMapper,
        private ContributorResponseMapper $responseMapper,
    ) {
    }

    #[Route('/contributors', name: 'contributors_list', methods: ['GET'])]
    public function __invoke(
        int $repositoryId,
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        ContributorListQueryDto $queryDto,
    ): JsonResponse {
        $request = $this->requestMapper->mapToApplication($repositoryId, $queryDto);
        $response = ($this->handler)($request);

        return new JsonResponse($this->responseMapper->listResponse($response));
    }
}
