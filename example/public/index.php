<?php
session_start();

$basePath = '/var/www/test';
set_include_path(__DIR__ . "/../lib");

require_once('AutoLoad.php');

$request = new Request();
$app = new App($basePath);
$app->run($request);


