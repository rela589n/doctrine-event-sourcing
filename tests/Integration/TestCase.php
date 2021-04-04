<?php

declare(strict_types=1);

namespace Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupEntityManager();
    }

    private function setupEntityManager(): void
    {
        static $em;

        if (null === $em){
            ['entity_manager' => $em] = require __DIR__.'/../../bootstrap/tests.php';
        }

        $this->entityManager = $em;
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        shell_exec(__DIR__.'/../../vendor/bin/doctrine orm:schema-tool:create --quiet');
    }

    public static function tearDownAfterClass(): void
    {
        shell_exec(__DIR__.'/../../vendor/bin/doctrine orm:schema-tool:drop --force --quiet');

        parent::tearDownAfterClass();
    }
}
