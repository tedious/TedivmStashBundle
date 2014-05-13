<?php

/*
 * This file is part of the StashBundle package.
 *
 * (c) Josh Hall-Bachner <jhallbachner@gmail.com>
 * (c) Robert Hafner <tedivm@tedivm.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('TESTING', true);
define('TESTING_DIRECTORY', __DIR__);
error_reporting(-1);

date_default_timezone_set('UTC');

$filename = __DIR__ .'/../vendor/autoload.php';

if (!file_exists($filename)) {
    echo "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~" . PHP_EOL;
    echo " You need to execute `composer install` before running the tests. " . PHP_EOL;
    echo "         Vendors are required for complete test execution.        " . PHP_EOL;
    echo "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~" . PHP_EOL . PHP_EOL;
    $filename = __DIR__ .'/../autoload.php';
}

$loader = require $filename;
$loader->addPsr4('Tedivm\\StashBundle\\Test\\', __DIR__);
$loader->addPsr4('Stash\\Test\\', __DIR__ . '/../vendor/tedivm/stash/tests/Stash/Test/');
