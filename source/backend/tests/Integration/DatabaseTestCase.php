<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Boots the kernel against the pre-provisioned test database. DAMA
 * DoctrineTestBundle wraps each test in a transaction that is rolled back
 * at teardown, preserving the shared fixture data for the next test.
 */
abstract class DatabaseTestCase extends KernelTestCase
{
    protected EntityManagerInterface $em;

    #[Override]
    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.orm.default_entity_manager');
        $this->em = $em;
    }
}
