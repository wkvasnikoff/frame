#!/bin/env php
<?php

$skeletonDir = __DIR__ . '/skeleton';
$destination = realpath(__DIR__ . '/../..') . '/';
$cmd = "cp -r $skeletonDir/* $destination";

`$cmd`;
echo "Done\n";
