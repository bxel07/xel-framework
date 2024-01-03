<?php

use Setup\bootstrap\bootstrap_rev_1;


require __DIR__."/../vendor/autoload.php";



/**
 * Container injector
 */


/**
 * Dynamic Router Loader
 */


/**
 * DB Pool Connection
 */



/**
 * Launch Http Server and database connection
 */

// $app = new bootstrap_rev_1();
// $app->setupServer()->run();

// Example of usage
$bootstrap = (new bootstrap_rev_1())->setupServer();
$bootstrap->run();

