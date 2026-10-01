<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Doctrine\Repository;

use App\RepositoryInsights\Domain\Entity\Contributor;
use App\RepositoryInsights\Domain\Repository\ContributorRepository;
use App\RepositoryInsights\Domain\ValueObject\ContributorIdentity;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineContributorRepository implements ContributorRepository
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function save(Contributor $contributor): void
    {
        $this->em->persist($contributor);
    }

    public function findByIdentity(ContributorIdentity $identity): ?Contributor
    {
        return $this->em->getRepository(Contributor::class)->findOneBy([
            'identity' => $identity->value,
        ]);
    }
}
