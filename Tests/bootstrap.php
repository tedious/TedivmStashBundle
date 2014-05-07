<?php

$local = __DIR__ .'/../vendor/autoload.php';
$installed = __DIR__ .'/../../../../../autoload.php';

if (file_exists($local)) {
    require_once $local;
} elseif (file_exists($installed)) {
    require_once $installed;
} else {
    throw new Exception("You need to execute `composer install` before running the tests. (vendors are required for test execution)");
}
