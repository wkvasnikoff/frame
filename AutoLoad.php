<?php

function __autoload($class)
{
    $parts = explode('\\', $class);

    if ($parts[0] === 'frame') {
        array_shift($parts);
    }
    $path = '' . join('/', $parts) . '.php';

    require_once($path);
}
