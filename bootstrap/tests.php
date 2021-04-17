<?php

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use DoctrineExtensions\Types\CarbonDateTimeTzType;
use Ramsey\Uuid\Doctrine\UuidType;

require_once __DIR__.'/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/..', '.env.testing');
$env = $dotenv->load();

$entityManager = EntityManager::create(
    [
        'host' => $env['DB_HOST'],
        'port' => $env['DB_PORT'],
        'user' => $env['DB_USERNAME'],
        'password' => $env['DB_PASSWORD'],
        'dbname' => $env['DB_DATABASE'],
        'driver' => $env['DB_DRIVER'],
    ],
    Setup::createXMLMetadataConfiguration(
        [
            __DIR__.'/../config/mappings',
            __DIR__.'/../tests/Integration/DomainMock/Chat/mappings',
            __DIR__.'/../tests/Integration/DomainMock/User/mappings',
            __DIR__.'/../tests/Integration/DomainMock/Message/mappings',
        ],
        true,
        __DIR__.'/../tmp/doctrine',
    ),
);

Type::hasType('uuid')
|| Type::addType('uuid', UuidType::class);

Type::hasType(CarbonDateTimeTzType::CARBONDATETIMETZ)
|| Type::addType(CarbonDateTimeTzType::CARBONDATETIMETZ, CarbonDateTimeTzType::class);

return [
    'entity_manager' => $entityManager,
];

