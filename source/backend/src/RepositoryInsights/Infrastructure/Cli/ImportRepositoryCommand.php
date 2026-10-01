<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Cli;

use App\RepositoryInsights\Application\UseCase\ImportRepository\ImportRepositoryHandler;
use App\RepositoryInsights\Application\UseCase\ImportRepository\ImportRepositoryRequest;
use App\RepositoryInsights\Domain\Enum\GitProvider;
use App\RepositoryInsights\Domain\ValueObject\RepositoryCoordinates;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:import', description: 'Import commits from a git provider.')]
final class ImportRepositoryCommand extends Command
{
    public function __construct(private readonly ImportRepositoryHandler $handler)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('coords', InputArgument::REQUIRED, 'owner/repo (e.g. symfony/symfony)')
            ->addOption('provider', 'p', InputOption::VALUE_REQUIRED, 'Provider id', GitProvider::Github->value)
            ->addOption('max', 'm', InputOption::VALUE_REQUIRED, 'Max commits to fetch', '1000');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $slug = (string) $input->getArgument('coords');
        $providerId = (string) $input->getOption('provider');
        $max = (int) $input->getOption('max');

        try {
            $provider = GitProvider::from($providerId);
            $coords = RepositoryCoordinates::fromSlug($provider, $slug);
        } catch (\Throwable $throwable) {
            $io->error($throwable->getMessage());

            return Command::INVALID;
        }

        $io->section(sprintf('Importing %s from %s (up to %d commits)', $coords->getFullName(), $provider->value, $max));

        $result = ($this->handler)(new ImportRepositoryRequest($coords, $max));

        $io->success(sprintf(
            'Done. Repo #%d — seen=%d inserted=%d skipped=%d',
            $result->repositoryId, $result->seen, $result->inserted, $result->skippedDuplicates,
        ));

        return Command::SUCCESS;
    }
}
