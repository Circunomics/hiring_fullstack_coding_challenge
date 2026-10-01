<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Application\UseCase\ImportRepository;

use App\RepositoryInsights\Application\Dto\RemoteCommit;
use App\RepositoryInsights\Application\Service\CommitProviderRegistry;
use App\RepositoryInsights\Domain\Entity\Commit;
use App\RepositoryInsights\Domain\Entity\Contributor;
use App\RepositoryInsights\Domain\Entity\TrackedRepository;
use App\RepositoryInsights\Domain\Repository\CommitRepository;
use App\RepositoryInsights\Domain\Repository\ContributorRepository;
use App\RepositoryInsights\Domain\Repository\TrackedRepositoryRepository;
use App\RepositoryInsights\Domain\ValueObject\ContributorIdentity;
use App\RepositoryInsights\Domain\ValueObject\RepositoryCoordinates;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;
use Webmozart\Assert\Assert;
final readonly class ImportRepositoryHandler
{
    private const int BATCH_SIZE = 100;

    private LoggerInterface $log;

    public function __construct(
        private EntityManagerInterface $em,
        private TrackedRepositoryRepository $repositories,
        private ContributorRepository $contributors,
        private CommitRepository $commits,
        private CommitProviderRegistry $providers,
        ?LoggerInterface $log = null,
    ) {
        $this->log = $log ?? new NullLogger();
    }

    public function __invoke(ImportRepositoryRequest $request): ImportRepositoryResponse
    {
        $coords = $request->coordinates;
        $repository = $this->repositories->findByCoordinates($coords)
            ?? $this->registerRepository($coords);

        $repository->beginSync();

        $this->em->flush();

        $existingShas = array_fill_keys($this->commits->existingShasFor($repository), true);
        /** @var array<string, Contributor> $contribCache */
        $contribCache = [];

        $seen = 0;
        $inserted = 0;
        $skipped = 0;

        try {
            $providerClient = $this->providers->get($coords->provider);
            foreach ($providerClient->fetchRecentCommits($coords, $request->maxCommits) as $remote) {
                $seen++;

                if (isset($existingShas[$remote->sha->value])) {
                    $skipped++;
                    continue;
                }

                $contributor = $this->resolveContributor($remote, $contribCache);

                $commit = new Commit(
                    repository: $repository,
                    contributor: $contributor,
                    sha: $remote->sha,
                    gitAuthorName: $remote->authorName,
                    gitAuthorEmail: $remote->authorEmail,
                    message: $remote->message,
                    committedAt: $remote->committedAt,
                    htmlUrl: $remote->htmlUrl,
                );
                $this->commits->save($commit);
                $existingShas[$remote->sha->value] = true;
                $inserted++;

                if ($inserted % self::BATCH_SIZE === 0) {
                    $this->em->flush();
                }
            }

            $this->em->flush();
            $repository->completeSync(new DateTimeImmutable());
            $this->em->flush();

            $this->log->info('Import complete', [
                'repo' => $coords->getFullName(), 'seen' => $seen, 'inserted' => $inserted, 'skipped' => $skipped,
            ]);

            $id = $repository->getId();
            Assert::integer($id, 'Repository id must exist after flush.');

            return new ImportRepositoryResponse($id, $seen, $inserted, $skipped);
        } catch (Throwable $throwable) {
            $this->em->flush(); // persist whatever we managed to insert
            $repository->failSync($throwable->getMessage());
            $this->em->flush();

            $this->log->warning('Import failed', [
                'repo' => $coords->getFullName(), 'error' => $throwable->getMessage(),
                'seen' => $seen, 'inserted' => $inserted,
            ]);

            throw $throwable;
        }
    }

    private function registerRepository(RepositoryCoordinates $coords): TrackedRepository
    {
        $repository = new TrackedRepository($coords);
        $this->repositories->save($repository);
        $this->em->flush();

        return $repository;
    }

    /**
     * @param array<string, Contributor> $cache
     */
    private function resolveContributor(RemoteCommit $remote, array &$cache): Contributor
    {
        $identity = ContributorIdentity::fromEmailAndName($remote->authorEmail, $remote->authorName);

        if (isset($cache[$identity->value])) {
            $cache[$identity->value]->fillAvatarIfMissing($remote->authorAvatarUrl);

            return $cache[$identity->value];
        }

        $contributor = $this->contributors->findByIdentity($identity);
        if (!$contributor instanceof Contributor) {
            $contributor = new Contributor($identity, $remote->authorName, $remote->authorEmail);
            $contributor->fillAvatarIfMissing($remote->authorAvatarUrl);
            $this->contributors->save($contributor);
            $this->em->flush();
        } else {
            $contributor->fillAvatarIfMissing($remote->authorAvatarUrl);
        }

        $cache[$identity->value] = $contributor;

        return $contributor;
    }
}
