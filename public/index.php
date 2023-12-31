<?php

use Setup\bootstrap\bootstrap;
require __DIR__."/../vendor/autoload.php";

$app = new bootstrap();

/**
 * Container injector
 */


/**
 * Dynamic Router Loader
 */


/**
 * DB Pool Connection
 */
$connectionDriver = require __DIR__.'/../setup/config/database.php';

/**
 * Launch Http Server and database connection
 */
$app
    ->setupDatabasePool()
    ->setupServer()
    ->run();