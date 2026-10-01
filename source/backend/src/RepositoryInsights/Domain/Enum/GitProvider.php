<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Domain\Enum;

enum GitProvider: string
{
    case Github = 'github';
}
