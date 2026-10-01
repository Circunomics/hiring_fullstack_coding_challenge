<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Doctrine\Repository;

use App\RepositoryInsights\Domain\Entity\Commit;
use App\RepositoryInsights\Domain\Entity\TrackedRepository;
use App\RepositoryInsights\Domain\Repository\CommitRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

final readonly class DoctrineCommitRepository implements CommitRepository
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function save(Commit $commit): void
    {
        $this->em->persist($commit);
    }

    public function existingShasFor(TrackedRepository $repository): array
    {
        /** @var list<array{sha:string}> $rows */
        $rows = $this->em->createQueryBuilder()
            ->from(Commit::class, 'c')
            ->select('c.sha')
            ->where('c.repository = :r')
            ->setParameter('r', $repository)
            ->getQuery()
            ->getScalarResult();

        return array_map(static fn (array $row): string => (string) $row['sha'], $rows);
    }

    public function aggregateContributors(
        TrackedRepository $repository,
        ?string $search,
        ?DateTimeImmutable $from,
        ?DateTimeImmutable $to,
        string $sort,
        string $direction,
        int $page,
        int $perPage,
    ): array {
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $base = $this->em->createQueryBuilder()
            ->from(Commit::class, 'c')
            ->innerJoin('c.contributor', 'u')
            ->where('c.repository = :r')
            ->setParameter('r', $repository);
        $this->applyDateFilters($base, $from, $to);
        $this->applySearch($base, $search);

        $itemsQb = (clone $base)
            ->select(
                'u.id AS id',
                'u.displayName AS displayName',
                'u.email AS email',
                'u.avatarUrl AS avatarUrl',
                'COUNT(c.id) AS commitCount',
                'MAX(c.committedAt) AS lastCommittedAt',
            )
            ->groupBy('u.id');

        $sortMap = [
            'commits' => 'commitCount',
            'name' => 'displayName',
            'lastCommittedAt' => 'lastCommittedAt',
        ];
        $itemsQb->orderBy($sortMap[$sort] ?? 'commitCount', $direction)
            ->addOrderBy('u.id', 'ASC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        /** @var list<array<string, mixed>> $rawItems */
        $rawItems = $itemsQb->getQuery()->getArrayResult();

        $items = array_map(static function (array $row): array {
            $committedAt = $row['lastCommittedAt'];
            if (!$committedAt instanceof DateTimeImmutable) {
                $committedAt = new DateTimeImmutable((string) $committedAt);
            }

            return [
                'id' => (int) $row['id'],
                'displayName' => (string) $row['displayName'],
                'email' => $row['email'] !== null ? (string) $row['email'] : null,
                'avatarUrl' => $row['avatarUrl'] !== null ? (string) $row['avatarUrl'] : null,
                'commitCount' => (int) $row['commitCount'],
                'lastCommittedAt' => $committedAt,
            ];
        }, $rawItems);

        $total = (int) (clone $base)
            ->select('COUNT(DISTINCT u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return ['items' => $items, 'total' => $total];
    }

    public function commitsByContributor(
        TrackedRepository $repository,
        int $contributorId,
        int $page,
        int $perPage,
    ): array {
        $base = $this->em->createQueryBuilder()
            ->from(Commit::class, 'c')
            ->where('c.repository = :r')
            ->andWhere('c.contributor = :u')
            ->setParameter('r', $repository)
            ->setParameter('u', $contributorId);

        /** @var list<array<string, mixed>> $rawItems */
        $rawItems = (clone $base)
            ->select(
                'c.id AS id',
                'c.sha AS sha',
                'c.message AS message',
                'c.committedAt AS committedAt',
                'c.htmlUrl AS htmlUrl',
                'c.gitAuthorName AS authorName',
                'c.gitAuthorEmail AS authorEmail',
            )
            ->orderBy('c.committedAt', 'DESC')
            ->addOrderBy('c.id', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getArrayResult();

        $items = array_map(static fn(array $row): array => [
            'id' => (int) $row['id'],
            'sha' => (string) $row['sha'],
            'message' => (string) $row['message'],
            'committedAt' => $row['committedAt'],
            'htmlUrl' => (string) $row['htmlUrl'],
            'authorName' => (string) $row['authorName'],
            'authorEmail' => $row['authorEmail'] !== null ? (string) $row['authorEmail'] : null,
        ], $rawItems);

        $total = (int) (clone $base)
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return ['items' => $items, 'total' => $total];
    }

    private function applyDateFilters(QueryBuilder $qb, ?DateTimeImmutable $from, ?DateTimeImmutable $to): void
    {
        if ($from instanceof \DateTimeImmutable) {
            $qb->andWhere('c.committedAt >= :from')->setParameter('from', $from);
        }

        if ($to instanceof \DateTimeImmutable) {
            $qb->andWhere('c.committedAt < :to')->setParameter('to', $to);
        }
    }

    private function applySearch(QueryBuilder $qb, ?string $search): void
    {
        if ($search === null || trim($search) === '') {
            return;
        }

        $qb->andWhere('LOWER(u.displayName) LIKE :q OR LOWER(u.email) LIKE :q')
            ->setParameter('q', '%' . strtolower($search) . '%');
    }
}
