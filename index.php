<?php

use Dotenv\Dotenv;
use Ions\app\Core\Application;

/**
 * ---*******---*******
 * Index Page.
 * ---******---********
 */

require __DIR__ . "/vendor/autoload.php";

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

require __DIR__ . "/app/Core/function.php";


$app = new Application();

$app->run();