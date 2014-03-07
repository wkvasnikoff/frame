<?php
session_start();

$basePath = realpath(__DIR__ . '/..');
set_include_path("$basePath/lib:$basePath/frame");

require_once('AutoLoad.php');

$request = new frame\Request();
$app = new frame\App($basePath);
$app->run($request);
