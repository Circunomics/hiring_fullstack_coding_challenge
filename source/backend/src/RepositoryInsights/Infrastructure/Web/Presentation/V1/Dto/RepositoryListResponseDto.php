<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Web\Presentation\V1\Dto;

final readonly class RepositoryListResponseDto
{
    /** @param list<RepositoryListItemResponseDto> $items */
    public function __construct(public array $items)
    {
    }
}
