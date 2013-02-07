<?php

namespace Tedivm\StashBundle\Factory;
use Stash\Drivers;

class HandlerFactory {

    static function createHandler($types, $options)
    {
        $handlers = Drivers::getDrivers();

        $h = array();

        foreach($types as $type) {
            $class = $handlers[$type];
            $opts = isset($options[$type]) ? $options[$type] : array();
            $h[] = new $class($opts);
        }

        if(count($h) == 1) {
            return reset($h);
        }

        $class = $handlers['Composite'];
        $handler = new $class(array('drivers' => $h));

        return $handler;
    }
}
