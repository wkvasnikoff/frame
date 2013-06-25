<?php
session_start();

$basePath = '/var/www/test';
set_include_path("$basePath/website/lib");

require_once('AutoLoad.php');

$request = new Request();
$app = new App($basePath);
$app->run($request);


