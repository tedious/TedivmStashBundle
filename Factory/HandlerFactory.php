<?php

namespace Tedivm\StashBundle\Factory;
use StashHandlers;

class HandlerFactory {

    static function createHandler($type, $options)
    {
        $handlers = StashHandlers::getHandlers();

        $class = $handlers[$type];

        if($type !== 'MultiHandler') {
            return new $class($options);
        }

        $h = array();
        $subhandlers = isset($options['handlers']) ? $options['handlers'] : array();
        foreach($subhandlers as $subhandler) {
            $shoptions = isset($options[$subhandler]) ? $options[$subhandler] : array();
            $h[] = self::createHandler($subhandler, $shoptions);
        }
        $options['handlers'] = $h;

        return new $class($options);
    }
}
