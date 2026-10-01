<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Mapper;

use App\RepositoryInsights\Application\UseCase\ListContributors\ListContributorsRequest;
use App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto\ContributorListQueryDto;
use DateTimeImmutable;

final readonly class ContributorListRequestMapper
{
    public function mapToApplication(int $repositoryId, ContributorListQueryDto $query): ListContributorsRequest
    {
        return new ListContributorsRequest(
            repositoryId: $repositoryId,
            search: $query->search,
            from: $this->parseDate($query->from),
            to: $this->parseDate($query->to, endOfDay: true),
            sort: $query->sort,
            direction: $query->direction,
            page: $query->page,
            perPage: $query->perPage,
        );
    }

    private function parseDate(?string $raw, bool $endOfDay = false): ?DateTimeImmutable
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        try {
            $date = new DateTimeImmutable($raw);
            if ($endOfDay) {
                $isDateOnly = preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw) === 1;

                return $date->modify($isDateOnly ? '+1 day' : '+1 microsecond');
            }

            return $date;
        } catch (\Exception $exception) {
            throw new \InvalidArgumentException(sprintf('Invalid date "%s".', $raw), $exception->getCode(), previous: $exception);
        }
    }
}
