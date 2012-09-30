<?php

namespace Tedivm\StashBundle\Factory;
use Stash\Handlers;

class HandlerFactory {

    static function createHandler($types, $options)
    {
        $handlers = Handlers::getHandlers();

        $h = array();

        foreach($types as $type) {
            $class = $handlers[$type];
            $opts = isset($options[$type]) ? $options[$type] : array();
            $h[] = new $class($opts);
        }

        if(count($h) == 1) {
            return reset($h);
        }

        $class = $handlers['MultiHandler'];
        $handler = new $class(array('handlers' => $h));

        return $handler;
    }
}
