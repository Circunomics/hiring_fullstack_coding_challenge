<?php

declare(strict_types=1);

use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(__DIR__ . '/../.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// Verify the configured test database before API/integration tests use it.
// The database is provisioned and populated before PHPUnit runs; DAMA wraps
// every individual test in a transaction and rolls it back afterward.
if (($_SERVER['APP_ENV'] ?? 'test') === 'test' && \in_array('--testsuite=unit', $_SERVER['argv'] ?? [], true) === false) {
    $kernel = new Kernel('test', (bool) ($_SERVER['APP_DEBUG'] ?? true));
    $kernel->boot();
    /** @var EntityManagerInterface $em */
    $em = $kernel->getContainer()->get('doctrine.orm.default_entity_manager');
    $connectionParams = $em->getConnection()->getParams();
    $databaseName = basename((string) ($connectionParams['dbname'] ?? $connectionParams['path'] ?? ''));
    $databaseName = preg_replace('/\.db$/i', '', $databaseName) ?? $databaseName;
    $testToken = (string) ($_SERVER['TEST_TOKEN'] ?? $_ENV['TEST_TOKEN'] ?? '');
    if (!str_ends_with($databaseName, '_test' . $testToken)) {
        throw new RuntimeException(sprintf(
            'Refusing to rebuild a database that is not clearly a test database (got "%s").',
            $databaseName,
        ));
    }

    $kernel->shutdown();
}
