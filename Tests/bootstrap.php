<?php

require_once $_SERVER['SYMFONY_SRC'].'/Symfony/Component/ClassLoader/UniversalClassLoader.php';

$loader = new Symfony\Component\ClassLoader\UniversalClassLoader();
$loader->registerNamespace('Symfony', $_SERVER['SYMFONY_SRC']);
$loader->register();

require_once( $_SERVER['SYMFONY_SRC'].'/../../vendor/tedivm/Stash/Autoloader.class.php');
\StashAutoloader::register();

spl_autoload_register(function($class)
{
    if (0 === strpos($class, 'Tedivm\\StashBundle')) {
        $path = implode('/', array_slice(explode('\\', $class), 2)).'.php';
        require_once __DIR__.'/../'.$path;
        return true;
    }
});
