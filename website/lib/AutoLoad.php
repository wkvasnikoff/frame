<?php

function __autoload($class)
{
	$parts = explode('\\', $class);
	$path = '' . join('/', $parts) . '.php';
	require_once($path);
}


