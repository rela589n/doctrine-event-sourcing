<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;

// replace with file to your own project bootstrap
$conf = require __DIR__ .'/../bootstrap/tests.php';

// replace with mechanism to retrieve EntityManager in your app
$entityManager = $conf['entity_manager'];

return ConsoleRunner::createHelperSet($entityManager);
