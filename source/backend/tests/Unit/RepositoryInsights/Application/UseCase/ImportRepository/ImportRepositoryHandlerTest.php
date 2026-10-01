<?php

declare(strict_types=1);

namespace App\Tests\Unit\RepositoryInsights\Application\UseCase\ImportRepository;

use App\RepositoryInsights\Application\Service\CommitProviderRegistry;
use App\RepositoryInsights\Application\UseCase\ImportRepository\ImportRepositoryHandler;
use App\RepositoryInsights\Application\UseCase\ImportRepository\ImportRepositoryRequest;
use App\RepositoryInsights\Domain\Entity\Commit;
use App\RepositoryInsights\Domain\Entity\Contributor;
use App\RepositoryInsights\Domain\Entity\TrackedRepository;
use App\RepositoryInsights\Domain\Enum\GitProvider;
use App\RepositoryInsights\Domain\Repository\CommitRepository;
use App\RepositoryInsights\Domain\Repository\ContributorRepository;
use App\RepositoryInsights\Domain\Repository\TrackedRepositoryRepository;
use App\RepositoryInsights\Domain\ValueObject\ContributorIdentity;
use App\RepositoryInsights\Domain\ValueObject\RepositoryCoordinates;
use App\Tests\Fixtures\Builder\RemoteCommitBuilder;
use App\Tests\Fixtures\Provider\RecordingGitProvider;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

#[CoversClass(ImportRepositoryHandler::class)]
final class ImportRepositoryHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<TrackedRepositoryRepository> */
    private ObjectProphecy $repositories;

    /** @var ObjectProphecy<ContributorRepository> */
    private ObjectProphecy $contributors;

    /** @var ObjectProphecy<CommitRepository> */
    private ObjectProphecy $commits;

    private RecordingGitProvider $provider;

    private EntityManagerInterface $entityManager;

    /** @var ObjectProphecy<TrackedRepository> */
    private ObjectProphecy $repository;

    protected function setUp(): void
    {
        $this->repositories = $this->prophesize(TrackedRepositoryRepository::class);
        $this->contributors = $this->prophesize(ContributorRepository::class);
        $this->commits = $this->prophesize(CommitRepository::class);
        $this->provider = new RecordingGitProvider([]);
        $this->repository = $this->prophesize(TrackedRepository::class);
        // PHPStan cannot infer Prophecy's dynamic __call-based method DSL.
        // @phpstan-ignore method.notFound
        $this->repository->getId()->willReturn(17);

        // @phpstan-ignore method.notFound
        $this->repositories->findByCoordinates(Argument::type(RepositoryCoordinates::class))
            ->willReturn($this->repository->reveal());
        // @phpstan-ignore method.notFound
        $this->contributors->findByIdentity(Argument::type(ContributorIdentity::class))
            ->willReturn(null);

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        // @phpstan-ignore method.notFound
        $entityManager->flush();
        $this->entityManager = $entityManager->reveal();
    }

    #[TestDox('imports commits once and skips them on a repeated import')]
    public function testImportsAndSkipsExistingCommits(): void
    {
        $shas = [str_repeat('a', 40), str_repeat('b', 40)];
        $this->provider->withCommits([
            RemoteCommitBuilder::create()->withSha($shas[0])->withAuthor('Alice', 'Alice@example.com')->build(),
            RemoteCommitBuilder::create()->withSha($shas[1])->withAuthor('Alice', 'alice@example.com')->build(),
        ]);
        // @phpstan-ignore method.notFound
        $this->repository->beginSync()->shouldBeCalledTimes(2);
        // @phpstan-ignore method.notFound
        $this->repository->completeSync(Argument::type(\DateTimeImmutable::class))->shouldBeCalledTimes(2);
        // @phpstan-ignore method.notFound
        $this->commits->existingShasFor($this->repository->reveal())->willReturn([], $shas)->shouldBeCalledTimes(2);
        // @phpstan-ignore method.notFound
        $this->commits->save(Argument::type(Commit::class))->shouldBeCalledTimes(2);
        // @phpstan-ignore method.notFound
        $this->contributors->save(Argument::type(Contributor::class))->shouldBeCalledOnce();

        $handler = new ImportRepositoryHandler(
            em: $this->entityManager,
            repositories: $this->repositories->reveal(),
            contributors: $this->contributors->reveal(),
            commits: $this->commits->reveal(),
            providers: new CommitProviderRegistry([$this->provider]),
        );
        $request = new ImportRepositoryRequest(new RepositoryCoordinates(GitProvider::Github, 'octo', 'demo'), 100);

        $firstImport = $handler($request);
        $secondImport = $handler($request);

        self::assertSame(17, $firstImport->repositoryId);
        self::assertSame(2, $firstImport->inserted);
        self::assertSame(0, $firstImport->skippedDuplicates);
        self::assertSame(0, $secondImport->inserted);
        self::assertSame(2, $secondImport->skippedDuplicates);
        self::assertSame(2, $this->provider->callCount);
    }
}
