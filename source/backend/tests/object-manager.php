<?php

// PHPStan doctrine extension entry — boots the kernel and returns the EM.

declare(strict_types=1);

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../.env')) {
    (new Dotenv())->bootEnv(__DIR__ . '/../.env');
}

$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test';
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = '1';

$kernel = new Kernel('test', true);
$kernel->boot();

/** @var \Doctrine\ORM\EntityManagerInterface $em */
$em = $kernel->getContainer()->get('doctrine.orm.default_entity_manager');

return $em;
