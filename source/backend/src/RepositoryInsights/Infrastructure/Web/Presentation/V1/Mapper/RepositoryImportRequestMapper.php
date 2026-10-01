<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Mapper;

use App\RepositoryInsights\Application\UseCase\ImportRepository\ImportRepositoryRequest;
use App\RepositoryInsights\Domain\Enum\GitProvider;
use App\RepositoryInsights\Domain\ValueObject\RepositoryCoordinates;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto\RepositoryImportRequestDto;
use InvalidArgumentException;

final readonly class RepositoryImportRequestMapper
{
    public function mapToApplication(RepositoryImportRequestDto $request): ImportRepositoryRequest
    {
        $provider = GitProvider::from($request->provider);

        if ($request->slug !== null) {
            $coordinates = RepositoryCoordinates::fromSlug($provider, $request->slug);
        } elseif ($request->owner !== null && $request->name !== null) {
            $coordinates = new RepositoryCoordinates($provider, $request->owner, $request->name);
        } else {
            throw new InvalidArgumentException('Expected a slug or both owner and name.');
        }

        return new ImportRepositoryRequest($coordinates, $request->maxCommits);
    }
}
