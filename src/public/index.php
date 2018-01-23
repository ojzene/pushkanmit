<?php
require '../../vendor/autoload.php';
use App\Config\Configuration;

set_include_path('../../src');
$mode = "debug";
// this could be production or debug or maintenance
$setting = (new Configuration)->config($mode);
$app = new \Slim\App($setting);
$app->add(new \CorsSlim\CorsSlim());

$container = $app->getContainer();
require '../Config/Database.php';
require '../Config/RedisDatabase.php';
require '../Config/Auth.php';
require '../Config/Dependencies.php';
require '../Routes/Routes.php';
$app->run();