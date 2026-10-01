<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Domain\Repository;

use App\RepositoryInsights\Domain\Entity\Contributor;
use App\RepositoryInsights\Domain\ValueObject\ContributorIdentity;

interface ContributorRepository
{
    public function save(Contributor $contributor): void;

    public function findByIdentity(ContributorIdentity $identity): ?Contributor;
}
