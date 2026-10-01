<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Domain\Enum;

enum SyncStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Success = 'success';
    case Failed = 'failed';
}
