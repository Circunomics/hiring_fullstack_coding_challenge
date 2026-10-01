<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Web\Controller\V1;

use App\RepositoryInsights\Application\UseCase\ListContributorCommits\ListContributorCommitsHandler;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Mapper\ContributorResponseMapper;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto\ContributorCommitsQueryDto;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Mapper\ContributorCommitsRequestMapper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/repositories/{repositoryId<\\d+>}', name: 'api_v1_repository_')]
final readonly class ContributorCommitsController
{
    public function __construct(
        private ListContributorCommitsHandler $handler,
        private ContributorCommitsRequestMapper $requestMapper,
        private ContributorResponseMapper $responseMapper,
    ) {
    }

    #[Route('/contributors/{contributorId<\\d+>}/commits', name: 'contributor_commits', methods: ['GET'])]
    public function __invoke(
        int $repositoryId,
        int $contributorId,
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        ContributorCommitsQueryDto $queryDto,
    ): JsonResponse {
        $request = $this->requestMapper->mapToApplication($repositoryId, $contributorId, $queryDto);
        $response = ($this->handler)($request);

        return new JsonResponse($this->responseMapper->commitsResponse($response));
    }
}
