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

define('DS', DIRECTORY_SEPARATOR);
define('ROOTPATH', __DIR__. DIRECTORY_SEPARATOR);

require __DIR__ . "/config/config.php";
require __DIR__ . "/app/Core/functions.php";

DEBUG ? ini_set('display_errors', 1) : ini_set('display_errors', 0);

$ACTIONS = [];
$FILTERS = [];
$APP['URL'] = split_url($_GET['url'] ?? 'home');
$APP['permissions'] = [];
$USER_DATA = [];

/**
 * Load Plugins.
 */

$PLUGINS = get_plugin_folders();
if (!load_plugins($PLUGINS))
    dnd("<center><h1 style='font-family:tahoma;margin-top:15px;'>No plugins were found!, Please put atleast one plugin in the Plugins folder</h1></center>");

$APP['permissions'] = do_filter('permissions', $APP['permissions']);

$app = new Application();

$app->run();
